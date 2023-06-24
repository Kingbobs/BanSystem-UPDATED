<?php

namespace bansystem;

use bansystem\command\BanCommand;
use bansystem\command\BanIPCommand;
use bansystem\command\BanListCommand;
use bansystem\command\BlockCommand;
use bansystem\command\BlockIPCommand;
use bansystem\command\BlockListCommand;
use bansystem\command\KickCommand;
use bansystem\command\MuteCommand;
use bansystem\command\MuteIPCommand;
use bansystem\command\MuteListCommand;
use bansystem\command\PardonCommand;
use bansystem\command\PardonIPCommand;
use bansystem\command\TBanCommand;
use bansystem\command\TBanIPCommand;
use bansystem\command\TBlockCommand;
use bansystem\command\TBlockIPCommand;
use bansystem\command\TMuteCommand;
use bansystem\command\TMuteIPCommand;
use bansystem\command\UnbanCommand;
use bansystem\command\UnbanIPCommand;
use bansystem\command\UnblockCommand;
use bansystem\command\UnblockIPCommand;
use bansystem\command\UnmuteCommand;
use bansystem\command\UnmuteIPCommand;
use bansystem\listener\PlayerChatListener;
use bansystem\listener\PlayerCommandPreproccessListener;
use bansystem\listener\PlayerPreLoginListener;
use pocketmine\event\Listener;
use pocketmine\permission\Permission;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;

class BanSystem extends PluginBase {
    
    private function removeCommand(string $command): void {
        $commandMap = $this->getServer()->getCommandMap();
        $cmd = $commandMap->getCommand($command);
        if ($cmd === null) {
            return;
        }
        $cmd->setLabel("");
        $commandMap->unregister($cmd);
    }
    
    private function initializeCommands(): void {
        $commands = ["ban", "banlist", "pardon", "pardon-ip", "ban-ip", "kick"];
        foreach ($commands as $command) {
            $this->removeCommand($command);
        }
        $commandMap = $this->getServer()->getCommandMap();
        $commandMap->registerAll("bansystem", [
            new BanCommand(),
            new BanIPCommand(),
            new BanListCommand(),
            new BlockCommand(),
            new BlockIPCommand(),
            new BlockListCommand(),
            new KickCommand(),
            new MuteCommand(),
            new MuteIPCommand(),
            new MuteListCommand(),
            new PardonCommand(),
            new PardonIPCommand(),
            new TBanCommand(),
            new TBanIPCommand(),
            new TBlockCommand(),
            new TBlockIPCommand(),
            new TMuteCommand(),
            new TMuteIPCommand(),
            new UnbanCommand(),
            new UnbanIPCommand(),
            new UnblockCommand(),
            new UnblockIPCommand(),
            new UnmuteCommand(),
            new UnmuteIPCommand()
        ]);
    }
    
    /**
     * @param Permission[] $permissions
     */
    protected function addPermissions(array $permissions): void {
        foreach ($permissions as $permission) {
            $this->getServer()->getPluginManager()->addPermission($permission);
        }
    }
    
    /**
     * 
     * @param Plugin $plugin
     * @param Listener[] $listeners
     */
    protected function registerListeners(Plugin $plugin, array $listeners): void {
        foreach ($listeners as $listener) {
            $this->getServer()->getPluginManager()->registerEvents($listener, $plugin);
        }
    }
    
    private function initializeListeners(): void {
        $this->registerListeners($this, [
            new PlayerChatListener(),
            new PlayerCommandPreproccessListener(),
            new PlayerPreLoginListener()
        ]);
    }
    
    private function initializeFiles(): void {
        @mkdir($this->getDataFolder());
        if (!file_exists($this->getDataFolder() . "muted-players.txt")) {
            @touch($this->getDataFolder() . "muted-players.txt");
        }
        if (!file_exists($this->getDataFolder() . "muted-ips.txt")) {
            @touch($this->getDataFolder() . "muted-ips.txt");
        }
        if (!file_exists($this->getDataFolder() . "blocked-players.txt")) {
            @touch($this->getDataFolder() . "blocked-players.txt");
        }
        if (!file_exists($this->getDataFolder() . "blocked-ips.txt")) {
            @touch($this->getDataFolder() . "blocked-ips.txt");
        }
    }
    
    private function initializePermissions(): void {
        $this->addPermissions([
            new Permission("bansystem.command.ban", "Allows the player to prevent the given player from using this server.", Permission::DEFAULT_OP),
            new Permission("bansystem.command.banip", "Allows the player to prevent the given IP address from using this server.", Permission::DEFAULT_OP),
            new Permission("bansystem.command.banlist", "Allows the player to view the players/IP addresses banned on this server.", Permission::DEFAULT_OP),
            new Permission("bansystem.command.blocklist", "Allows the player to view all the players/IP addresses banned from this server."),
            new Permission("bansystem.command.kick", "Allows the player to remove the given player.", Permission::DEFAULT_OP),
            new Permission("bansystem.command.mute", "Allows the player to prevent the given player from sending public chat messages.", Permission::DEFAULT_OP),
            new Permission("bansystem.command.muteip", "Allows the player to prevent the given IP address from sending public chat messages.", Permission::DEFAULT_OP),
            new Permission("bansystem.command.mutelist", "Allows the player to view all the players muted on this server.", Permission::DEFAULT_OP),
            new Permission("bansystem.command.pardon", "Allows the player to allow the given player to use this server.", Permission::DEFAULT_OP),
            new Permission("bansystem.command.pardonip", "Allows the player to allow the given IP address to use this server.", Permission::DEFAULT_OP),
            new Permission("bansystem.command.tban", "Allows the player to temporarily prevent the given player from using this server.", Permission::DEFAULT_OP),
            new Permission("bansystem.command.tbanip", "Allows the player to temporarily prevent the given IP address from using this server.", Permission::DEFAULT_OP),
            new Permission("bansystem.command.tmute", "Allows the player to temporarily prevent the given player from sending public chat messages.", Permission::DEFAULT_OP),
            new Permission("bansystem.command.tmuteip", "Allows the player to temporarily prevent the given IP address from sending public chat messages.", Permission::DEFAULT_OP),
            new Permission("bansystem.command.unban", "Allows the player to allow the given player to use this server.", Permission::DEFAULT_OP),
            new Permission("bansystem.command.unbanip", "Allows the player to allow the given IP address to use this server.", Permission::DEFAULT_OP),
            new Permission("bansystem.command.unmute", "Allows the player to allow the given player to send public chat messages.", Permission::DEFAULT_OP),
            new Permission("bansystem.command.unmuteip", "Allows the player to allow the given IP address to send public chat messages.")
        ]);
    }
    
    private function removeBanExpired(): void {
        $this->getServer()->getNameBans()->removeExpired();
        $this->getServer()->getIPBans()->removeExpired();
        Manager::getNameMutes()->removeExpired();
        Manager::getIPMutes()->removeExpired();
        Manager::getNameBlocks()->removeExpired();
        Manager::getIPBlocks()->removeExpired();
    }
    
    public function onLoad(): void {
        $this->getLogger()->info("VMPE-Action is now loading... Please wait for completion.");
    }
    
    public function onEnable(): void {
        $this->getLogger()->info("VMPE-Action is now enabled. As far as we know, there are no errors on enable.");
        $this->initializeCommands();
        $this->initializeListeners();
        $this->initializePermissions();
        $this->initializeFiles();
        $this->removeBanExpired();
    }
    
    public function onDisable(): void {
        $this->getLogger()->info("VMPE-Action is now disabled. Did the server stop?");
    }
}
