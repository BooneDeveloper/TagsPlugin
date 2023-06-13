<?php

namespace BooneDev;

use BooneDev\factory\TagsFactory;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class TagsPlugin extends PluginBase implements Listener {

    private $tagsFactory;
    private $playerTags;

    public function onEnable(): void {
        $this->tagsFactory = new TagsFactory($this);
        $this->playerTags = new Config($this->getDataFolder() . "player_tags.yml", Config::YAML, []);

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($command->getName() === "tag") {
            if (isset($args[0])) {
                $subCommand = strtolower($args[0]);
                switch ($subCommand) {
                    case "create":
                        if (isset($args[1])) {
                            $tagName = $args[1];
                            if ($this->tagsFactory->getTag($tagName) === null) {
                                $formattedTag = TextFormat::colorize("&7[&r" . $tagName . "&7]&r");
                                $this->tagsFactory->createTag($tagName, $formattedTag);
                                $sender->sendMessage(TextFormat::GREEN . "Se ha creado la etiqueta '" . $tagName . "'.");
                            } else {
                                $sender->sendMessage(TextFormat::RED . "Ya existe una etiqueta con ese nombre.");
                            }
                        } else {
                            $sender->sendMessage(TextFormat::RED . "Debes especificar un nombre para la etiqueta.");
                        }
                        break;

                    case "list":
                        $tags = $this->tagsFactory->getAllTags();
                        $tagList = implode(", ", $tags);
                        $sender->sendMessage(TextFormat::YELLOW . "Etiquetas disponibles: " . $tagList);
                        break;

                    case "delete":
                        if (isset($args[1])) {
                            $tagName = $args[1];
                            if ($this->tagsFactory->getTag($tagName) !== null) {
                                $this->tagsFactory->deleteTag($tagName);
                                $sender->sendMessage(TextFormat::GREEN . "Se ha eliminado la etiqueta '" . $tagName . "'.");
                            } else {
                                $sender->sendMessage(TextFormat::RED . "No existe una etiqueta con ese nombre.");
                            }
                        } else {
                            $sender->sendMessage(TextFormat::RED . "Debes especificar el nombre de la etiqueta a eliminar.");
                        }
                        break;

                    case "set":
                        if ($sender instanceof Player) {
                            if (isset($args[1])) {
                                $tagName = $args[1];
                                $tag = $this->tagsFactory->getTag($tagName);
                                if ($tag !== null) {
                                    $success = $this->tagsFactory->setPlayerTag($sender, $tagName);
                                    if ($success) {
                                        $sender->sendMessage(TextFormat::GREEN . "Se te ha asignado la etiqueta '" . $tagName . "'.");
                                    } else {
                                        $sender->sendMessage(TextFormat::RED . "No se pudo asignar la etiqueta. Asegúrate de que la etiqueta exista.");
                                    }
                                } else {
                                    $sender->sendMessage(TextFormat::RED . "No existe una etiqueta con ese nombre.");
                                }
                            } else {
                                $sender->sendMessage(TextFormat::RED . "Debes especificar el nombre de la etiqueta a asignar.");
                            }
                        } else {
                            $sender->sendMessage(TextFormat::RED . "Este comando solo puede ser ejecutado por un jugador.");
                        }
                        break;

                    case "remove":
                        if ($sender instanceof Player) {
                            $this->tagsFactory->removePlayerTag($sender);
                            $sender->sendMessage(TextFormat::GREEN . "Se ha eliminado tu etiqueta asignada.");
                        } else {
                            $sender->sendMessage(TextFormat::RED . "Este comando solo puede ser ejecutado por un jugador.");
                        }
                        break;

                    default:
                        $sender->sendMessage(TextFormat::RED . "Comando desconocido. Usa /tag create, /tag list, /tag delete, /tag set o /tag remove.");
                        break;
                }
            } else {
                $sender->sendMessage(TextFormat::RED . "Debes especificar un subcomando. Usa /tag create, /tag list, /tag delete, /tag set o /tag remove.");
            }
            return true;
        }
        return false;
    }

    public function onPlayerChat(PlayerChatEvent $event) {
        $player = $event->getPlayer();
        $tag = $this->tagsFactory->getPlayerTag($player);
        if ($tag !== null) {
            $message = $event->getMessage();
            $formattedTag = $tag->getFormattedTag();
            $event->setMessage($formattedTag . " " . $message);
        }
    }

    public function getPlayerTags(): Config {
        return $this->playerTags;
    }
    
}