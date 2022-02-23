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
        $this->releases = $this->getReleases();
    }

    public function getReleases(): Collection
    {
        $response = Http::withHeaders(['Authorization' => "token $this->token"])
                          ->get("https://api.github.com/repos/$this->owner/$this->name/releases");
        return $response->throw()->collect();
    }

    public function getLatestVersion(string $branch)
    {
        return $this->releases->firstWhere('target_commitish', $branch)['tag_name'] ?? null;
    }

    public function getZip(string $version): string
    {
        $zipLink = $this->releases->firstWhere('tag_name', $version)['zipball_url'] ?? null;
        $response = Http::withHeaders(['Authorization' => "token $this->token"])->get($zipLink);
        return $response->body();
    }
}
