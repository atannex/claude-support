<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport\Sync;

use Atannex\ClaudeSupport\Guidelines\ManagedBlock;
use Illuminate\Filesystem\Filesystem;
use stdClass;

/**
 * Diffs the desired state against disk and the manifest, then applies the result.
 *
 * The one rule: a file whose current hash differs from the hash we last wrote
 * has been edited by someone, and is never replaced without --force.
 */
final class Synchronizer
{
    public function __construct(
        private readonly Filesystem $files,
        private readonly string $root,
    ) {}

    public function plan(DesiredState $desired, Manifest $manifest, bool $force = false): Plan
    {
        $changes = [];

        foreach ($desired->files as $path => $contents) {
            $changes[] = $this->planFile($path, $contents, $manifest, $force);
        }

        foreach ($manifest->paths() as $path) {
            if (! array_key_exists($path, $desired->files)) {
                $changes[] = $this->planOrphan($path, $manifest, $force, $desired->uninstall);
            }
        }

        if (($guidelines = $this->planGuidelines($desired)) !== null) {
            $changes[] = $guidelines;
        }

        [$settings, $permissions] = $this->planSettings($desired, $manifest);
        if ($settings !== null) {
            $changes[] = $settings;
        }

        return new Plan($changes, $permissions, $desired->uninstall);
    }

    public function apply(Plan $plan, Manifest $manifest): void
    {
        foreach ($plan->changes as $change) {
            $absolute = $this->root.'/'.$change->path;

            if ($change->action->writes()) {
                $this->files->ensureDirectoryExists(dirname($absolute));
                $this->files->put($absolute, (string) $change->contents);
            }

            match ($change->action) {
                Action::Create, Action::Update, Action::Overwrite, Action::Adopt, Action::Unchanged => $change->tracked
                    ? $manifest->record($change->path, (string) $change->contents)
                    : null,
                Action::Remove => $this->remove($absolute, $change, $manifest),
                Action::Release => $manifest->forget($change->path),
                Action::Skip => null,
            };
        }

        if ($plan->permissions !== null) {
            $manifest->setPermissions($plan->permissions);
        }

        if ($plan->uninstall) {
            $this->files->delete($this->root.'/'.Manifest::PATH);
            $this->pruneEmptyDirectories($this->root.'/'.Manifest::PATH);
        } else {
            $manifest->save($this->files, $this->root);
        }
    }

    private function planFile(string $path, string $contents, Manifest $manifest, bool $force): Change
    {
        if ($this->escapesRoot($path)) {
            return $this->outside($path);
        }

        $current = $this->read($path);
        $known = $manifest->hashOf($path);

        if ($current === null) {
            return new Change($path, Action::Create, $contents);
        }

        if (Manifest::same($current, $contents)) {
            return new Change($path, $known === Manifest::hash($current) ? Action::Unchanged : Action::Adopt, $contents);
        }

        if ($known === Manifest::hash($current)) {
            return new Change($path, Action::Update, $contents);
        }

        $why = $known === null ? 'exists and was not written by claude-support' : 'modified locally';

        return $force
            ? new Change($path, Action::Overwrite, $contents, $why)
            : new Change($path, Action::Skip, $contents, "{$why}; re-run with --force to replace it");
    }

    private function planOrphan(string $path, Manifest $manifest, bool $force, bool $uninstall): Change
    {
        if ($this->escapesRoot($path)) {
            return $this->outside($path);
        }

        $current = $this->read($path);

        if ($current === null) {
            return new Change($path, Action::Release, reason: 'already deleted');
        }

        if ($force || Manifest::hash($current) === $manifest->hashOf($path)) {
            return new Change($path, Action::Remove);
        }

        // Keep the user's edited copy. On install we stop tracking it; on uninstall the manifest goes anyway.
        return $uninstall
            ? new Change($path, Action::Skip, reason: 'modified locally, kept; re-run with --force to delete it')
            : new Change($path, Action::Release, reason: 'modified locally, kept and no longer managed');
    }

    private function planGuidelines(DesiredState $desired): ?Change
    {
        $path = $desired->guidelinesFile;
        if ($this->escapesRoot($path)) {
            return $this->outside($path);
        }

        $current = $this->read($path);

        if ($current !== null && ManagedBlock::broken($current)) {
            return new Change($path, Action::Skip, reason: 'its claude-support start and end markers do not pair up; fix them so the block can be updated safely', tracked: false);
        }

        if ($desired->guidelines === null) {
            if ($current === null || ! ManagedBlock::has($current)) {
                return null;
            }

            $next = ManagedBlock::strip($current);

            return $next === ''
                ? new Change($path, Action::Remove, tracked: false)
                : new Change($path, Action::Update, $next, tracked: false);
        }

        $next = ManagedBlock::splice($current ?? '', $desired->guidelines);

        return new Change($path, match (true) {
            $current === null => Action::Create,
            Manifest::same($current, $next) => Action::Unchanged,
            default => Action::Update,
        }, $next, tracked: false);
    }

    /**
     * Additive merge into permissions.allow. We only ever remove entries we added
     * ourselves; an entry the project already had is never claimed as ours.
     *
     * @return array{Change|null, list<string>|null}
     */
    private function planSettings(DesiredState $desired, Manifest $manifest): array
    {
        $path = $desired->settingsFile;
        if ($this->escapesRoot($path)) {
            return [$this->outside($path), null];
        }

        $raw = $this->read($path);
        $owned = $manifest->permissions();

        if ($raw === null && $desired->permissions === []) {
            return [null, []];
        }

        // Decode to objects, not arrays, so "{}" values elsewhere in the file survive re-encoding.
        $data = $raw === null ? new stdClass : json_decode($raw);
        $permissions = $data instanceof stdClass ? ($data->permissions ?? new stdClass) : null;
        $allow = $permissions instanceof stdClass ? ($permissions->allow ?? []) : null;

        if (! is_array($allow)) {
            return [new Change($path, Action::Skip, reason: 'not a JSON object with a permissions.allow list; fix it to let claude-support merge permissions', tracked: false), null];
        }

        $allow = array_values(array_filter($allow, 'is_string'));
        $stale = array_diff($owned, $desired->permissions);
        $next = array_values(array_diff($allow, $stale));
        foreach ($desired->permissions as $permission) {
            if (! in_array($permission, $next, true)) {
                $next[] = $permission;
            }
        }

        $ownedNext = array_values(array_unique([
            ...array_intersect($owned, $desired->permissions, $allow),
            ...array_diff($desired->permissions, $allow),
        ]));

        if ($next === $allow) {
            return [$raw === null ? null : new Change($path, Action::Unchanged, $raw, tracked: false), $ownedNext];
        }

        $data->permissions ??= new stdClass;
        if ($next === []) {
            unset($data->permissions->allow);
            if (get_object_vars($data->permissions) === []) {
                unset($data->permissions);
            }
        } else {
            $data->permissions->allow = $next;
        }

        if (get_object_vars($data) === []) {
            return [new Change($path, Action::Remove, tracked: false), $ownedNext];
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n";

        return [new Change($path, $raw === null ? Action::Create : Action::Update, $json, tracked: false), $ownedNext];
    }

    private function remove(string $absolute, Change $change, Manifest $manifest): void
    {
        $this->files->delete($absolute);
        $manifest->forget($change->path);
        $this->pruneEmptyDirectories($absolute);
    }

    /** Remove directories we emptied, walking up but never past the project root. */
    private function pruneEmptyDirectories(string $absolute): void
    {
        $dir = dirname($absolute);

        while ($dir !== $this->root && str_starts_with($dir, $this->root.'/')
            && $this->files->isDirectory($dir) && $this->files->isEmptyDirectory($dir)) {
            $this->files->deleteDirectory($dir);
            $dir = dirname($dir);
        }
    }

    /**
     * True when a symlink makes the path land outside the project root, e.g. a
     * cloned repository whose .claude/ points elsewhere. Links that stay inside
     * the project are fine.
     */
    private function escapesRoot(string $path): bool
    {
        $root = realpath($this->root);
        if ($root === false) {
            return false;
        }

        // Resolve the deepest part of the path that exists; what doesn't exist yet can't be a link.
        $existing = $this->root.'/'.$path;
        while (! file_exists($existing) && ! is_link($existing) && $existing !== $this->root) {
            $existing = dirname($existing);
        }

        $real = realpath($existing);

        return $real === false || ($real !== $root && ! str_starts_with($real, rtrim($root, '/\\').DIRECTORY_SEPARATOR));
    }

    private function outside(string $path): Change
    {
        return new Change($path, Action::Skip, reason: 'resolves through a symlink to outside the project; left untouched', tracked: false);
    }

    private function read(string $path): ?string
    {
        $absolute = $this->root.'/'.$path;

        return $this->files->isFile($absolute) ? (string) $this->files->get($absolute) : null;
    }
}
