<?php
namespace app\controllers;

use \app\config\Config;
use \app\models\OffersModel;
use \app\models\UsersModel;
use \app\models\PurseModel;
use \app\models\data\PurseOperationData;
use \app\models\PurseOperationsModel;
use \app\controllers\IndexController as Index;

/**
 *
 */
class CommandsExternalController
{
    const OHC = 'ohc'; // Offers Hour Checker - delete with cron
    const MD = 'mail_delivery'; // Mail Delivery

    public $params;

    public function run($params)
    {
        $this->params = $params;

        if (isset($this->params['k']) && isset($this->params['r']) && $this->params['k'] == Config::KEY) {
            if ($this->params['r'] == self::OHC) {
                self::actionOffersHourChecker();
            } elseif ($this->params['r'] == self::MD) {
                self::mailDelivery();
            } else {
                return;
            }
        } elseif (isset($this->params['sha1_hash']) && $this->params['sha1_hash'] == sha1($this->params['notification_type'] . '&' . $this->params['operation_id'] . '&' . $this->params['amount'] . '&' . $this->params['currency'] . '&' . $this->params['datetime'] . '&' . $this->params['sender'] . '&' . $this->params['codepro'] . '&' . Config::YANDEX_MONEY_HASH . '&' . $this->params['label'])) {
            self::notifyYandexMoney();
        }
    }

    public function mailDelivery()
    {
        $users_model = new UsersModel();
        $users = $users_model->getAll();

        foreach ($users as $one) {
            if ($one->username != null) {
                $link = 'Для просмотра отзывов о себе: <a href="' . Config::TG_LINK . Config::BOT_LINK . '?start=rate-' . $one->username . '">' . Config::TG_LINK . Config::BOT_LINK . '?start=rate-' . $one->username . '</a>';
            } else {
                $link = 'Пример отзывов участника доступен по ссылке: <a href="' . Config::TG_LINK . Config::BOT_LINK . '?start=rate-idamascus">' . Config::TG_LINK . Config::BOT_LINK . '?start=rate-idamascus</a>';
            }

            $index = new Index();
            $index->chat_id = $one->chat_id;
            $index->reply = 'Друзья, сегодня заблокируют Телеграм в России!' . PHP_EOL . PHP_EOL . 'Мы подготовили гайд, который поможет обойти блокировку: https://t.me/nahalyavu/903' . PHP_EOL . PHP_EOL . 'Подпишись на канал @nahalyavu - сегодня там опубликуем более расширенный мануал!' . PHP_EOL . PHP_EOL . 'Оставайтесь на связи!';            
            // $index->reply = '<b>Обновление МБиржи 1.00b</b>' . PHP_EOL . PHP_EOL . 'С сегодняшнего дня на бирже вводится абонентская плата за возможность продавать коды. Статус продавца - даёт право торговли на ограниченное время. <b>1 неделя - 100 рублей, 1 месяц - 250 рублей.</b>' . PHP_EOL . PHP_EOL . '- Добавлен внутренний кошелёк.' . PHP_EOL . '- Добавлен статус продавца.' . PHP_EOL . '- Пересмотрена стоимость VIP размещения. Теперь стоимость 60 рублей.' . PHP_EOL . PHP_EOL . '<b>Завтра стартует СМС акция:</b> https://t.me/nahalyavu/693 с региональными промокодами до 99% скидки.' . PHP_EOL . 'Появился <b>сервис мониторинга цен в М.Видео:</b> https://logprice.ru/ можно подписываться на изменение цены и следить за её динамикой на любой товар.' . PHP_EOL . PHP_EOL . 'Общаемся о М.Видео и продаём коды в чате: @mvideokuponchat' . PHP_EOL . 'Собрание всей полезной информации о М.Видео @mvideowiki' . PHP_EOL . PHP_EOL . '<i>С любовью, команда Мбиржа-бота!</i>';
            $buttons = [
                'keyboard' => [
                    ['Купить', 'Продать'],
                    ['Кошелёк', 'Топ 50'],
                    ['Услуги гаранта', 'Отзыв о продавце'],
                ],
                'resize_keyboard' => true
            ];
            $index->replyKeyboard = json_encode($buttons);
            $index->replyParse = true;
            $index->send();
        }
        return;
    }

    protected function actionOffersHourChecker()
    {
        $offers_model = new OffersModel();
        $offers = $offers_model->getAllToDelete();
        $offers_premium = $offers_model->getAllToDelete(['status' => OffersModel::STATUS_PREMIUM]);
        $offers_premium_half = $offers_model->getAllToDelete(['status' => OffersModel::STATUS_PREMIUM_HALF]);

        $offers_premium = array_merge($offers_premium, $offers_premium_half);
        $offers = array_merge($offers, $offers_premium);

        if ($offers != null) {
            foreach ($offers as $one) {
                $user = $one->user_id;
                $price = $one->price;
                $category = $one->category->title;
                $one->user_id = $user->id;
                if ($one->category->id == 1) {
                    $output = $one->rating->discount . '/' . $one->rating->discount_from . ' за ' . $one->price . ' - ' . $category;
                } elseif($one->category->id == 3) {
                    $output = $one->total . ' за ' . $one->price . ' - ' . $category;
                } elseif ($one->category->id == 2) {
                    $output = $one->title . ' за ' . $one->price . ' - ' . $category;
                }
                $payed_to = ($user->payed_to == 0) ? new \DateTime(date('Y-m-d H:i:s')) : new \DateTime($user->payed_to);
                $now = new \DateTime(date('Y-m-d H:i:s'));

                $result = $one->delete();
                if ($result != false) {
                    $index = new Index();
                    $index->chat_id = $user->chat_id;
                    $code = Config::getOfferCode($result);
                    if ($code != null) {
                        if ($price != 0) {
                            if ($payed_to > $now) {
                                $buttons['inline_keyboard'] = [
                                    [
                                        ['text' => 'Опубликовать повторно', 'callback_data' => '/sell?action=rePublish&offer_id=' . $result],
                                    ]
                                ];
                                $index->replyKeyboard = json_encode($buttons);
                            }
                            $index->reply = 'Ваше объявление <b>' . $code . ' - ' . $output . ' - </b> было автоматически удалено (закончился срок размещения). Вы можете опубликовать его повторно: /sell, и разместить на 12 часов воспользовавшись функцией премиум: /vip';
                            $index->send();
                        }
                    }
                }
            }
        }

        return;
    }

    public function notifyYandexMoney()
    {
        $model = new PurseOperationsModel();
        $data = new PurseOperationData();
        $id = (new Config())->decode($this->params['label']);
        $operation = $model->getOne(['where' => ['id = ' . $id, 'status <> ' . $data::STATUS_PAYED]]);

        $amount = (isset($this->params['withdraw_amount'])) ? $this->params['withdraw_amount'] : $this->params['amount'];

        if ($operation !== false) {
            if ((isset($this->params['unaccepted']) && $this->params['unaccepted'] === true) || $this->params['codepro'] === true) {
                $operation->status = $data::STATUS_PROBLEM;
            } else {
                $operation->status = $data::STATUS_PAYED;
                $operation->amount = $amount;
            }

            $operation = $model->update([
              'id' => $operation->id,
              'amount' => $operation->amount,
              'status' => $operation->status
            ]);

            $index = new Index();
            $index->chat_id = $operation->purse_id->user_id->chat_id;
            file_put_contents('test.txt', var_export($this->params, true));

            if ($operation->status == $data::STATUS_PAYED) {
                $operation->purse_id->balance = $operation->purse_id->balance + $amount;
                $purse = (new PurseModel())->update([
                  'id' => $operation->purse_id->id,
                  'balance' => $operation->purse_id->balance
                ]);
                $index->reply = 'Ваш кошелёк пополнен на: ' . $amount . ' руб.' . PHP_EOL . PHP_EOL . 'Ваш баланс: ' . $purse->balance . ' руб.';
                $index->send();
            } else {
                $index->reply = 'К сожалению, при пополнении вашего кошелька возникла ошибка. Пожалуйста обратитесь к администратору бота - @idamascus';
                $index->send();

            }
        }
    }
}
