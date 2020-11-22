<?php

namespace App\Services;

use App\Models\Mail as MailModel;
use Illuminate\Support\Facades\Redis;

use Illuminate\Support\Facades\Mail;

use App\Mail\MyMail;

class MailService
{
    protected $redis;

    /**
     * MailService constructor.
     * @param Redis $redis
     */
    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @param $to
     * @param $message
     * @return mixed
     */
    public function send($to, $message)
    {
        $redis_mails = $this->getRedisMails($to);
        $all_mails = $redis_mails;

        if (count($redis_mails) < count($to)) {
            $value_bd_mails = $this->getMailsFromBd($to, $redis_mails);
            foreach ($value_bd_mails as $key => $value_bd_mail) {
                $all_mails[$key] = $value_bd_mail;
            }

            $this->setRedisMails($value_bd_mails);
        }

        Mail::to($all_mails)->queue(new MyMail($message));
        if (Mail::failures()) {
            return response()->Fail('Sorry! Please try again latter');
        } else {
            return response()->success('Great! Successfully send in your mail');
        }
    }

    /**
     * @param $to
     * @param $redis_mails
     * @return array
     */
    protected function getMailsFromBd($to, $redis_mails)
    {
        $id_not_redis = [];
        foreach ($to as $id) {
            if (!isset($redis_mails[$id])) {
                $id_not_redis[$id] = $id;
            }
        }
        $bd_mails = MailModel::find($id_not_redis);
        $value_bd_mails = [];
        foreach ($bd_mails as $bd_mail) {
            $value_bd_mails[$bd_mail->id] = $bd_mail->mail;
        }

        return $value_bd_mails;
    }

    /**
     * @param $mails
     */
    protected function setRedisMails($mails)
    {
        Redis::pipeline(function ($pipe) use ($mails) {
            foreach ($mails as $id => $mail) {
                $pipe->set("mail:$id", $mail);
            }
        });
    }

    /**
     * @param $ids
     * @return array
     */
    protected function getRedisMails($ids)
    {
        $res = [];
        foreach ($ids as $id) {
            $value = Redis::get("mail:$id");
            if ($value) {
                $res[$id] = $value;
            }
        }

        return $res;
    }
}
