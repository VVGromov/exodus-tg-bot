<?php namespace app\components;

use \app\models\CommandsModel;
use \app\models\UsersModel;

/**
 *
 */
class MenuComponent
{
    public $object;

    function __construct($object)
    {
        $this->object = $object;
    }

    public function get()
    {

        $command = $this->object->command;

        $childs = (new CommandsModel())->getAll([
            'where' => [
                'in_menu = 1',
                'parent_id = ' . $command->id,
            ]
        ]);

        $parent = (new CommandsModel())->getOne([
            'where' => [
                'in_menu = 1',
                'id = ' . $command->parent_id,
            ]
        ]);

        $menu = [];

        if ($childs !== false) {
            $menu = $this->formatting($childs);
        } elseif (strpos($command->code, '/back') !== false && $parent !== false) {
            if ($parent->parent_id === 0) {
                $menu = $this->getDefaultMenu();
            } else {
                $childs = (new CommandsModel())->getAll([
                    'where' => [
                        'in_menu = 1',
                        'parent_id = ' . $parent->parent_id,
                    ]
                ]);

                $menu = $this->formatting($childs);
            }
        } elseif ($command->code == '/start') {
            $menu = $this->getDefaultMenu();
        }

        $buttons = [
            'keyboard' => $menu,
            'resize_keyboard' => true
        ];

        if (!empty($menu)) {
            return json_encode($buttons);
        } else {
            return false;
        }
    }

    private function getDefaultMenu()
    {
        $commands = (new CommandsModel())->getAll([
            'where' => [
                'in_menu = 1',
                'parent_id = 0',
            ]
        ]);

        $menu = [];

        if ($commands !== false) {
            $menu = $this->formatting($commands);
        }

        if (!empty($menu)) {
            return $menu;
        } else {
            return false;
        }
    }

    private function formatting($array)
    {
        $k = count($array) >= 5 ? 3 : 2;
        $col = $k;
        $i = 0;
        $row = 0;

        $menu = [];

        foreach ($array as $one) {
            $i++;
            if ($i > $col) {
                $row++;
                $col = $col + $k;
            }
            $menu[$row][] = $one->title;
        }

        return $menu;
    }
}
