<?php
namespace app\base;

use \app\components\ErrorsComponent as Errors;

/**
 *
 */
class Controller
{
    public $object;

    function __construct($object)
    {
        $this->object = $object;
    }

    public function run()
    {
        $action = substr($this->object->command->code, 1);

        if (method_exists($this, $action)) {
            return $this->$action();
        } else {
            return (new Errors($this->object))->getError('not-command');
        }
    }
}

?>
