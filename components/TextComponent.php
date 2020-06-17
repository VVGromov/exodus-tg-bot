<?php namespace app\components;

use app\config\Config;
use app\models\TagsModel as Tags;
use app\models\TransfersModel as Transfers;
use app\models\dir\DirTransferModel as DirTransfer;
use app\models\dir\DirCurrencyModel as DirCurrency;
use app\models\dir\DirObjectsPropertiesModel as DirObjectsProperties;
use app\models\ObjectsPropertiesModel as ObjectsProperties;
use app\models\UsersRequisitesModel;
use app\components\ReferralComponent;

/**
 *
 */
class TextComponent
{
    public $object;
    public $data = null;

    function __construct($object, $data = null)
    {
        $this->object = $object;
        $this->data = $data;
    }

    const TYPES = [
        'prev' => [
            'code' => '%prev%',
            'check' => 0
        ],
        'prev_object' => [
            'code' => '%prev_object%',
            'check' => 0
        ],
        'value' => [
            'code' => '%value%',
            'check' => 0
        ],
        'br' => [
            'code' => '%br%',
            'function' => 'Br',
            'check' => 1
        ],
        'status' => [
            'code' => '%status%',
            'function' => 'Status',
            'check' => 1
        ],
        'currency' => [
            'code' => '%currency%',
            'function' => 'Currency',
            'check' => 1
        ],
        'bot_name' => [
            'code' => '%bot_name%',
            'function' => 'BotName',
            'check' => 1
        ],
        'ref_link' => [
            'code' => '%ref_link%',
            'function' => 'RefLink',
            'check' => 1
        ],
        'tag_link' => [
            'code' => '%tag_link%',
            'function' => 'TagLink',
            'check' => 1
        ],
        'firstname' => [
            'code' => '%firstname%',
            'function' => 'Firstname',
            'check' => 1
        ],
        'lastname' => [
            'code' => '%lastname%',
            'function' => 'Lastname',
            'check' => 1
        ],
        'username' => [
            'code' => '%username%',
            'function' => 'Username',
            'check' => 1
        ],
        'properties' => [
            'code' => '%properties%',
            'function' => 'Properties',
            'check' => 1,
            'childs' => [
                'date_left' => [
                    'code' => '%date_left%',
                    'function' => 'DateLeft',
                ],
                'date_all' => [
                    'code' => '%date_all%',
                    'function' => 'DateAll',
                ],
                'date_period' => [
                    'code' => '%date_period%',
                    'function' => 'DatePeriod',
                ],
                'money_collected' => [
                    'code' => '%money_collected%',
                    'function' => 'MoneyCollected',
                ],
                'money_left' => [
                    'code' => '%money_left%',
                    'function' => 'MoneyLeft',
                ],
                'my_money_left' => [
                    'code' => '%my_money_left%',
                    'function' => 'MyMoneyLeft',
                ],
                'money_given' => [
                    'code' => '%money_given%',
                    'function' => 'MoneyGiven',
                ],
                'users_all' => [
                    'code' => '%users_all%',
                    'function' => 'UsersAll',
                ],
                'users_part' => [
                    'code' => '%users_part%',
                    'function' => 'UsersPart',
                ],
                'requisite' => [
                    'code' => '%requisite%',
                    'function' => 'Requisite',
                ],
            ]
        ]
    ];

    const TEMPLATE = [
        'start' => 'start',
        'view' => 'view',
        'history' => 'history',
        'query' => 'query',
        'new' => 'new',
        'wallet' => 'wallet',
        'notify_new' => 'notify_new',
        'button' => 'button',
        'reply_first' => 'reply_first',
        'button_must_action' => 'button_must_action',
        'finish_button' => 'finish_button',
        'not-confirm' => 'not-confirm'
    ];

    public function getText($text, $template = false)
    {
        if ($text !== NULL && $text !== false) {
            if ($template !== false) {
                $text = trim(preg_replace('/\s\s+/', ' ', $text));
                $array = json_decode($text, true);
                if (array_key_exists($template, self::TEMPLATE) && array_key_exists($template, $array)) {
                    $text = $array[$template];
                }
            }

            foreach (self::TYPES as $key => $type) {
                if ($type['check'] === 1 && strpos($text, $type['code']) !== false) {
                    $func = 'replace' . $type['function'];
                    $text = $this->$func($text);
                }
            }
        }

        return $text;
    }

    private function replaceBr($text)
    {
        return str_replace(self::TYPES['br']['code'], PHP_EOL, $text);
    }

    private function replaceStatus($text)
    {
        $value = '';

        if (isset($this->data['tag'])) {
            $tag = $this->data['tag'];
        } elseif (isset($this->data['user'])) {
            $tag = (new Tags())->getOne([
                'where' => [
                    'user_id = ' . $this->data['user']->id,
                ]
            ]);
        } else {
            $tag = (new Tags())->getOne([
                'where' => [
                    'user_id = ' . $this->object->user->id,
                    'status = 0'
                ],
                'order' => [
                    'id DESC'
                ]
            ]);
        }

        if ($tag !== false) {
            $value = $tag->color->title;
        }

        return str_replace(self::TYPES['status']['code'], $value, $text);
    }

    private function replaceCurrency($text)
    {
        if (isset($this->data['currency'])) {
            $currency = (new DirCurrency())->getOne([
                'where' => [
                    'id = ' . $this->data['currency']
                ]
            ]);
        } elseif (!isset($this->data['currency']) && isset($this->data['user'])) {
            $currency = (new DirCurrency())->getOne([
                'where' => [
                    'id = ' . $this->data['user']->currency_id,
                ]
            ]);
        } else {
            $currency = (new DirCurrency())->getOne([
                'where' => [
                    'id = ' . $this->object->user->currency_id,
                ]
            ]);
        }

        return str_replace(self::TYPES['currency']['code'], '(' . $currency->title . ')', $text);
    }

    private function replaceBotName($text)
    {
        return str_replace(self::TYPES['bot_name']['code'], Config::BOT_NAME, $text);
    }

    private function replaceFirstname($text)
    {
        if (isset($this->data['user']) && $this->data['user']->first_name !== '0') {
            return str_replace(self::TYPES['firstname']['code'], $this->data['user']->first_name, $text);
        } elseif (!isset($this->data['user']) && $this->object->user->first_name !== '0') {
            return str_replace(self::TYPES['firstname']['code'], $this->object->user->first_name, $text);
        } else {
            return str_replace(self::TYPES['firstname']['code'], '', $text);
        }
    }

    private function replaceLastname($text)
    {
        if (isset($this->data['user']) && $this->data['user']->last_name !== '0') {
            return str_replace(self::TYPES['lastname']['code'], $this->data['user']->last_name, $text);
        } elseif (!isset($this->data['user']) && $this->object->user->last_name !== '0') {
            return str_replace(self::TYPES['lastname']['code'], $this->object->user->last_name, $text);
        } else {
            return str_replace(self::TYPES['lastname']['code'], '', $text);
        }
    }

    private function replaceUsername($text)
    {
        if (isset($this->data['user']) && $this->data['user']->username !== '0') {
            return str_replace(self::TYPES['username']['code'], '@' . $this->data['user']->username, $text);
        } elseif (!isset($this->data['user']) && $this->object->user->username !== '0') {
            return str_replace(self::TYPES['username']['code'], '@' . $this->object->user->username, $text);
        } else {
            return str_replace(self::TYPES['username']['code'], '', $text);
        }
    }

    private function replaceRefLink($text)
    {
        $link = (new ReferralComponent($this->object))->getReferralLink($this->object->user->ref_hash);
        return str_replace(self::TYPES['ref_link']['code'], $link, $text);
    }

    private function replaceTagLink($text)
    {
        if ($this->data !== null && isset($this->data['tag'])) {
            $link = (new ReferralComponent($this->object))->getReferralLink($this->data['tag']->ref_hash);
            return str_replace(self::TYPES['tag_link']['code'], $link, $text);
        }
    }

    private function replaceProperties($text)
    {
        if (isset($this->data['parent']) && isset($this->data['parent_id'])) {
            $properties = (new ObjectsProperties())->getAll([
                'where' => [
                    'object = "' . $this->data['parent'] . '"',
                    'object_id = ' . $this->data['parent_id']
                ]
            ]);
            $properties_text = '';

            $new = new self($this->object, $this->data);

            if (isset($this->data['child_view']) && array_key_exists($this->data['child_view'], self::TEMPLATE)) {
                $view = $this->data['child_view'];
            } else {
                $view = self::TEMPLATE['view'];
            }

            if ($properties !== false) {
                foreach ($properties as $one) {
                    if ($one->dir->text !== false) {
                        if (strpos($one->dir->text, self::TYPES['value']['code'])) {
                            $one->dir->text = str_replace(self::TYPES['value']['code'], $one->value, $one->dir->text);
                        }

                        $one->dir->text = $new->getText($one->dir->text, $view);

                        foreach (self::TYPES['properties']['childs'] as $key => $type) {
                            if (strpos($one->dir->text, $type['code']) !== false) {
                                $func = 'propReplace' . $type['function'];
                                $one->dir->text = $this->$func($one);
                            }
                        }

                        $properties_text = $properties_text . $one->dir->text . PHP_EOL;
                    }
                }
            }

            $text = str_replace(self::TYPES['properties']['code'], $properties_text, $text);
        }
        return $text;
    }

    public function propReplaceRequisite($prop)
    {
        $req = (new UsersRequisitesModel())->getOne([
            'where' => [
                'id = ' . intval($prop->value),
            ]
        ]);
        return str_replace(self::TYPES['properties']['childs']['requisite']['code'], $req->type_id->title . ': <b>' . $req->number . '</b>', $prop->dir->text);
    }

    public function propReplaceDateLeft($prop)
    {
        $now = new \DateTime('now');
        $today = (new \DateTime('now'))->format('j');
        $date = (new \DateTime())->createFromFormat('d.m.Y', $prop->value);
        if ($prop->value === 'month') {
            $month = (new \DateTime('now'))->format('t');
            $value = $month - $today;
        } elseif ($prop->value === 'week') {
            $week = (new \DateTime('now'))->format('N');
            $value = 7 - $week;
        } elseif ($date && $date->format('d.m.Y') === $prop->value) {
            $value = $date->diff($now)->format('%a');
        } else {
            $month = (new \DateTime('now'))->format('t');
            $value = $month - $today;
        }

        return str_replace(self::TYPES['properties']['childs']['date_left']['code'], $value, $prop->dir->text);
    }

    public function propReplaceDateAll($prop)
    {
        $now = new \DateTime('now');
        if ($prop->value === 'month') {
            $value = (new \DateTime('now'))->format('t');
        } elseif ($prop->value === 'week') {
            $value = 7;
        } else {
            $date = (new \DateTime())->createFromFormat('d.m.Y', $prop->value);
            $start = (new \DateTime())->createFromFormat('Y-m-d H:i:s', $prop->created);
            if ($date !== false && $start !== false) {
                $value = $date->diff($start)->format('%a');
            } else {
                $value = 0;
            }
        }

        return str_replace(self::TYPES['properties']['childs']['date_all']['code'], $value, $prop->dir->text);
    }

    public function propReplaceDatePeriod($prop)
    {
        if ($prop->value === 'month') {
            $value = 'ежемесячно';
        } else {
            $value = 'еженедельно';
        }
        return str_replace(self::TYPES['properties']['childs']['date_period']['code'], $value, $prop->dir->text);
    }

    public function propReplaceMoneyCollected($prop)
    {
        $tag = (new Tags())->getOne([
            'where' => [
                'id = ' . $prop->object_id
            ]
        ]);
        $dir = (new DirTransfer())->getOne([
            'where' => [
                'code = "finished"'
            ]
        ]);
        $total = 0;

        if ($tag !== false) {
            $transfers = (new Transfers())->getAll([
                'where' => [
                    'tag_id = ' . $tag->id,
                    'status = ' . $dir->id
                ]
            ]);

            if ($transfers !== false) {
                foreach ($transfers as $one) {
                    $total = $total + $one->amount;
                }
            }
        }
        return str_replace(self::TYPES['properties']['childs']['money_collected']['code'], $total, $prop->dir->text);
    }

    public function propReplaceMoneyLeft($prop)
    {
        $tag = (new Tags())->getOne([
            'where' => [
                'id = ' . $prop->object_id
            ]
        ]);
        $dir = (new DirTransfer())->getOne([
            'where' => [
                'code = "finished"'
            ]
        ]);
        $total = 0;

        if ($tag !== false) {
            $transfers = (new Transfers())->getAll([
                'where' => [
                    'tag_id = ' . $tag->id,
                    'status = ' . $dir->id
                ]
            ]);

            if ($transfers !== false) {
                foreach ($transfers as $one) {
                    $total = $total + $one->amount;
                }
            }
            $total = $prop->value - $total;
        }
        return str_replace(self::TYPES['properties']['childs']['money_left']['code'], $total, $prop->dir->text);
    }

    public function propReplaceMyMoneyLeft($prop)
    {
        $tag = (new Tags())->getOne([
            'where' => [
                'user_id = ' . $this->object->user->id
            ]
        ]);
        $dir = (new DirTransfer())->getOne([
            'where' => [
                'code = "finished"'
            ]
        ]);
        $total = 0;

        if ($tag !== false) {
            $transfers = (new Transfers())->getAll([
                'where' => [
                    'user_from = ' . $this->object->user->id,
                    'status = ' . $dir->id,
                    'created >= "' . $tag->created . '"'
                ]
            ]);

            if ($transfers !== false) {
                foreach ($transfers as $one) {
                    $total = $total + $one->amount;
                }
            }
            $total = $prop->value - $total;
            $total = ($total < 0) ? 0 : $total;
        }
        return str_replace(self::TYPES['properties']['childs']['my_money_left']['code'], $total, $prop->dir->text);
    }

    public function propReplaceMoneyGiven($prop)
    {
        $dir = (new DirTransfer())->getOne([
            'where' => [
                'code = "finished"'
            ]
        ]);

        $tag = (new Tags())->getOne([
            'where' => [
                'user_id = ' . $this->object->user->id
            ]
        ]);

        $transfers = (new Transfers())->getAll([
            'where' => [
                'user_from = ' . $this->object->user->id,
                'status = ' . $dir->id,
                'created >= "' . $tag->created . '"'
            ]
        ]);

        $total = 0;
        if ($transfers !== false) {
            foreach ($transfers as $one) {
                $total = $total + $one->amount;
            }
        }
        return str_replace(self::TYPES['properties']['childs']['money_given']['code'], $total, $prop->dir->text);
    }

    public function propReplaceUsersAll($prop)
    {
        $tag = (new Tags())->getOne([
            'where' => [
                'id = ' . $prop->object_id
            ]
        ]);
        $dir = (new DirTransfer())->getOne([
            'where' => [
                'code = "refuse"'
            ]
        ]);
        $total = 0;

        if ($tag !== false) {
            $transfers = (new Transfers())->getAll([
                'where' => [
                    'tag_id = ' . $tag->id,
                    'status <> ' . $dir->id
                ]
            ]);

            $total = count($transfers);
        }
        return str_replace(self::TYPES['properties']['childs']['users_all']['code'], $total, $prop->dir->text);
    }

    public function propReplaceUsersPart($prop)
    {
        $tag = (new Tags())->getOne([
            'where' => [
                'id = ' . $prop->object_id
            ]
        ]);
        $dir = (new DirTransfer())->getOne([
            'where' => [
                'code = "refuse"'
            ]
        ]);

        $total = 0;

        if ($tag !== false) {
            $transfers = (new Transfers())->getAll([
                'where' => [
                    'tag_id = ' . $tag->id,
                    'status <> ' . $dir->id
                ]
            ]);

            if ($transfers !== false) {
                foreach ($transfers as $one) {
                    $total = $one->dir->code === 'finished' ? $total + 1 : $total;
                }
            }

        }
        return str_replace(self::TYPES['properties']['childs']['users_part']['code'], $total, $prop->dir->text);
    }
}
