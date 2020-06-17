<?php
namespace app\controllers;

use \app\controllers\UsersController as Users;
use \app\controllers\WalletController as Wallet;
use \app\controllers\StatusController as Status;
use \app\controllers\TransferController as Transfer;
use \app\controllers\TagsController as Tags;
use \app\controllers\ProfileController as Profile;
use \app\components\MenuComponent as Menu;
use \app\components\ErrorsComponent as Errors;
use \app\components\ReferralComponent;
use \app\components\StartComponent;
use \app\components\TextComponent as Text;
use \app\models\UsersRequisitesModel as Requisites;
use \app\models\TagsModel;
use \app\models\CommandsModel;
use \app\base\Controller;

/**
 *
 */
class CommandsController extends Controller
{

    public function run()
    {
        if ($this->checkToStart() !== false) {
            $menu = (new Menu($this->object))->get();

            if ($menu !== false) {
                $this->object->replyKeyboard = $menu;
            }
        } else {
            $this->object->command = (new CommandsModel())->getOne([
                'where' => [
                    'code = "/start"',
                ]
            ]);
            return $this->actionStart(true);
        }

        if (strpos($this->object->command->code, '/back') !== false) {
            $parent = (new CommandsModel())->getOne([
                'where' => [
                    'in_menu = 1',
                    'id = ' . $this->object->command->parent_id,
                ]
            ]);

            $grand = (new CommandsModel())->getOne([
                'where' => [
                    'in_menu = 1',
                    'id = ' . $parent->parent_id,
                ]
            ]);

            if ($parent === false || $grand === false) {
                $this->object->command->code = '/start';
            } else {
                $this->object->command->code = $grand->code;
            }
        }

        if (strpos($this->object->command->code, '/wallet') !== false) {
            return self::actionWallet();
        } elseif (strpos($this->object->command->code, '/status') !== false) {
            return self::actionStatus();
        } elseif (strpos($this->object->command->code, '/transfer') !== false) {
            return self::actionTransfer();
        } elseif (strpos($this->object->command->code, '/user') !== false) {
            return self::actionProfile();
        } elseif ($this->object->command->code) {
            $code = mb_substr($this->object->command->code, 1);
            $firstletter = mb_substr($code, 0, 1);
            $other = mb_substr($code, 1);
            $firstletter = mb_strtoupper($firstletter);
            $action = 'action' . $firstletter . $other;

            if (method_exists($this, $action)) {
                return self::$action();
            } else {
                return (new Errors($this->object))->getError('not-command');
            }
        } else {
            return (new Errors($this->object))->getError('not-command');
        }

    }

    protected function actionStart($new = false)
    {
        if ($new === true) {
            return (new StartComponent($this->object))->run();
        } else {
            return (new StartComponent($this->object))->index();            
        }
    }

    protected function actionWallet()
    {
        $wallet = new Wallet($this->object);
        return $wallet->run();
    }

    protected function actionTransfer()
    {
        $transfer = new Transfer($this->object);
        return $transfer->run();
    }

    protected function actionTags()
    {
        $tags = new Tags($this->object);
        return $tags->run();
    }

    protected function actionProfile()
    {
        $profile = new Profile($this->object);
        return $profile->run();
    }

    protected function actionStatus()
    {
        $status = new Status($this->object);
        return $status->run();
    }

    private function checkToStart()
    {
        $requisite = (new Requisites())->getOne([
            'where' => [
                'user_id = ' . $this->object->user->id
            ]
        ]);

        $tag = (new TagsModel())->getOne([
            'where' => [
                'user_id = ' . $this->object->user->id,
            ]
        ]);

        if ($this->object->user->currency_id == 0 || $requisite === false || $tag === false) {
            return false;
        }

        return true;
    }
}
