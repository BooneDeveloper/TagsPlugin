<?php

namespace BooneDev\factory;

use BooneDev\model\Tag;
use BooneDev\TagsPlugin;
use pocketmine\player\Player;

class TagsFactory {
    private $plugin;
    private $tags;

    public function __construct(TagsPlugin $plugin) {
        $this->plugin = $plugin;
        $this->tags = [];

        $this->loadTags();
    }

    public function getTag(string $tagName): ?Tag {
        return $this->tags[$tagName] ?? null;
    }

    public function getAllTags(): array {
        return array_keys($this->tags);
    }

    public function createTag(string $tagName, string $formattedTag): void {
        $this->tags[$tagName] = new Tag($tagName, $formattedTag);
        $this->saveTags();
    }

    public function deleteTag(string $tagName): void {
        unset($this->tags[$tagName]);
        $this->saveTags();

        // Eliminar la etiqueta de los jugadores que la tenían asignada
        $this->removeTagFromPlayers($tagName);
    }

    public function getPlayerTag(Player $player): ?Tag {
        $playerName = $player->getName();
        $tagName = $this->plugin->getPlayerTags()->get($playerName);
        if ($tagName !== null) {
            return $this->getTag($tagName);
        }

        return null;
    }

    public function setPlayerTag(Player $player, string $tagName): bool {
        if ($this->getTag($tagName) !== null) {
            $playerName = $player->getName();
            $this->plugin->getPlayerTags()->set($playerName, $tagName);
            $this->plugin->getPlayerTags()->save();
            return true;
        }

        return false;
    }

    public function removePlayerTag(Player $player): void {
        $playerName = $player->getName();
        $this->plugin->getPlayerTags()->remove($playerName);
        $this->plugin->getPlayerTags()->save();
    }

    private function loadTags(): void {
        $tagsData = $this->plugin->getConfig()->getAll();
        foreach ($tagsData as $tagName => $formattedTag) {
            $this->tags[$tagName] = new Tag($tagName, $formattedTag);
        }
    }

    private function saveTags(): void {
        $tagsData = [];
        foreach ($this->tags as $tagName => $tag) {
            $tagsData[$tagName] = $tag->getFormattedTag();
        }
        $this->plugin->getConfig()->setAll($tagsData);
        $this->plugin->getConfig()->save();
    }

    private function removeTagFromPlayers(string $tagName): void {
        $playerTags = $this->plugin->getPlayerTags()->getAll();
        foreach ($playerTags as $playerName => $assignedTag) {
            if ($assignedTag === $tagName) {
                $this->plugin->getPlayerTags()->remove($playerName);
            }
        }
        $this->plugin->getPlayerTags()->save();
    }
}
