<?php

namespace XiDanko\UpdateManager;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class GithubRepository
{
    private Collection $releases;
    private string $owner, $name, $token;

    public function __construct()
    {
        $this->owner = config('update-manager.repo_owner');
        $this->name = config('update-manager.repo_name');
        $this->token = config('update-manager.access_token');
        $this->releases = collect([]);
    }

    public function getReleases(): Collection
    {
        if ($this->releases->isEmpty()) {
            $response = Http::withToken($this->token)->get("https://api.github.com/repos/$this->owner/$this->name/releases");
            $this->releases = $response->throw()->collect();
        }
        return $this->releases;
    }

    public function getLatestRelease(string $branch)
    {
        return $this->getReleases()->firstWhere('target_commitish', $branch) ?? null;
    }

    public function getLatestVersion(string $branch)
    {
        return $this->getReleases()->firstWhere('target_commitish', $branch)['tag_name'] ?? null;
    }

    public function getZip(string $version): string
    {
        $zipLink = $this->getReleases()->firstWhere('tag_name', $version)['zipball_url'] ?? null;
        $response = Http::withToken($this->token)->get($zipLink);
        return $response->body();
    }
}
