<?php

namespace App\Http\Controllers;

use App\Models\Mail;
use App\Services\MailService;

use App\Http\Requests\MailRequest;

class MailController extends Controller
{
    protected $mail_service;

    /**
     * MailController constructor.
     * @param MailService $mail_service
     */
    public function __construct(MailService $mail_service)
    {
        $this->mail_service = $mail_service;
    }

    /**
     * @param MailRequest $request
     * @return string|void
     */
    public function index(MailRequest $request)
    {
        $params = $request->all();
        if (is_array($params['to'])) {
            return $this->sendMessages($request);
        } else {
            return $this->sendMessage($request);
        }
    }

    /**
     * @param MailRequest $request
     * @return string
     */
    public function sendMessage(MailRequest $request)
    {
        //метод который уже реализован

        return 'message sent';
    }

    /**
     * @param MailRequest $request
     */
    public function sendMessages(MailRequest $request)
    {
        $params = $request->all();

        $this->mail_service->send($params['to'], $params['message']);
    }
}
