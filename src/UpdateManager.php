<?php

namespace XiDanko\UpdateManager;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use XiDanko\UpdateManager\Events\UpdateFinished;
use XiDanko\UpdateManager\Events\UpdateStarted;
use XiDanko\UpdateManager\Events\UpgradeFinished;
use XiDanko\UpdateManager\Events\UpgradeStarted;

class UpdateManager
{
    private GithubRepository $repository;

    public function __construct(GithubRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getCurrentVersion()
    {
        return DB::table('updates')->latest()->first()->version ?? config('update-manager.init_version');
    }

    public function getLatestVersion()
    {
        $currentBranch = Str::of($this->getCurrentVersion())->after('v')->before('.')->append('.x')->toString();
        return $this->repository->getLatestVersion($currentBranch);
    }

    public function isNewVersionAvailable(): bool
    {
        return version_compare($this->getLatestVersion(), $this->getCurrentVersion(), '>');
    }

    public function getNextUpgradeVersion()
    {
        $branch = Str::of($this->getCurrentVersion())->after('v')->before('.')->toString();
        $branch = (int) $branch + 1;
        $branch .= ".x";
        return $this->repository->getLatestVersion($branch);
    }

    public function isUpgradeAvailable()
    {
        return (bool) $this->getNextUpgradeVersion();
    }

    public function updateToLatestVersion()
    {
        Event::dispatch(new UpdateStarted($this->getCurrentVersion(), $this->getLatestVersion()));
        $latestVersion = $this->getLatestVersion();
        $this->update($latestVersion);
        Event::dispatch(new UpdateFinished($this->getCurrentVersion(), $this->getLatestVersion()));
    }

    public function upgrade()
    {
        Event::dispatch(new UpgradeStarted($this->getCurrentVersion(), $this->getNextUpgradeVersion()));
        $upgradeVersion = $this->getNextUpgradeVersion();
        $this->update($upgradeVersion);
        Event::dispatch(new UpgradeFinished($this->getCurrentVersion(), $this->getLatestVersion()));
    }

    private function update(string $version)
    {
        $this->download($version);
        $this->extract($version);
        $this->copyFiles($version);
        $this->deleteTmpDirectory();
        DB::table('updates')->insert(['previous_version' => $this->getCurrentVersion(), 'version' => $version, 'created_at' => now(), 'updated_at' => now()]);
    }

    private function download(string $version)
    {
        Storage::put("/tmp/$version.zip", $this->repository->getZip($version));
    }

    private function extract(string $version)
    {
        $zip = new \ZipArchive();
        $zip->open(Storage::path("/tmp/$version.zip"));
        $zip->extractTo(Storage::path("/tmp/$version"));
        $zip->close();
    }

    private function copyFiles(string $version)
    {
        $updateDirectory = glob(Storage::path("/tmp/$version/*/"))[0];
        File::copyDirectory($updateDirectory, base_path());
    }

    private function deleteTmpDirectory()
    {
        File::deleteDirectory(Storage::path('/tmp'));
    }
}
