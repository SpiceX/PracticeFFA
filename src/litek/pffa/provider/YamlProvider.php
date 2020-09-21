<?php


namespace litek\pffa\provider;


use litek\pffa\PracticeFFA;

class YamlProvider
{
    /**@var PracticeFFA */
    private $plugin;

    /**
     * PracticeListener constructor.
     * @param PracticeFFA $plugin
     */
    public function __construct(PracticeFFA $plugin)
    {
        $this->plugin = $plugin;
        $this->init();
    }

    public function init(): void
    {
        @mkdir($this->plugin->getDataFolder() . 'arenas');
        $this->plugin->saveResource('database.sq3');
    }
}