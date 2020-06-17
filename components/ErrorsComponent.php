<?php namespace app\components;

use \app\models\dir\DirErrorsModel;

/**
 *
 */
class ErrorsComponent
{
    public $object;

    function __construct($object)
    {
        $this->object = $object;
    }

    public function getError($code = 'error')
    {
        $error = (new DirErrorsModel())->getOne([
            'where' => [
                'code = "' . $code . '"',
            ]
        ]);

        if ($error !== false) {
            $this->object->reply = $error->title;
        } else {
            $error = self::getDefaultError();
            $this->object->reply = $error->title;
        }

        return $this->object->send();
    }

    public function getText($code = 'error')
    {
        $error = (new DirErrorsModel())->getOne([
            'where' => [
                'code = "' . $code . '"',
            ]
        ]);

        if ($error === false) {
            $error = self::getDefaultError();
        }

        return $error->title;
    }

    private function getDefaultError()
    {
        $error = (new DirErrorsModel())->getOne([
            'where' => [
                'code = "error"',
            ]
        ]);

        return $error;
    }
}
