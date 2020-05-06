<?php

class UpdateChangelog
{
    const CHANGELOG_FILE = 'CHANGELOG.md';
    const GITHUB_URL = 'https://github.com/Apen/additional_reports/';
    const GITHUB_TAGS = 'https://api.github.com/repos/Apen/additional_reports/git/refs/tags';

    protected $version;
    protected $versionDate;

    public function __construct()
    {
        $this->version = trim(shell_exec('git describe --tags --abbrev=0'));
        $this->versionDate = trim(shell_exec('git log -1 --format=%ai ' . $this->version));
    }

    public function generate()
    {
        $changelog = '';

        $changelog .= 'Latest release : ' . $this->version . ' (' . $this->versionDate . ")\r\n";
        $changelog .= "\r\n\r\n";

        $allTags = json_decode($this->getUrl(self::GITHUB_TAGS), true);
        $allTagsContent = [];
        $lastTag = '';
        for ($i = 0; $i < count($allTags); $i++) {
            $tag = str_replace('refs/tags/', '', $allTags[$i]['ref']);
            $tagDate = trim(shell_exec('git log -1 --format=%ai ' . $tag));
            if ($i === 0) {
                array_unshift(
                    $allTagsContent,
                    '* ' . $tag . ' (' . $tagDate . ' First release'
                );
            } else {
                array_unshift(
                    $allTagsContent,
                    '* ' . $tag . ' (' . $tagDate . ') [Full list of changes](' . self::GITHUB_URL . 'compare/' . $lastTag . '...' . $tag . ')'
                );
            }
            $lastTag = $tag;
        }

        $changelog .= implode("\r\n", $allTagsContent);
        file_put_contents(self::CHANGELOG_FILE, $changelog);
    }

    public function getUrl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'ApenBot');
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }
}

call_user_func(function () {
    $changelog = new UpdateChangelog();
    $changelog->generate();
});
