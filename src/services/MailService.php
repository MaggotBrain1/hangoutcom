<?php

namespace App\services;

use Mailjet\Client;
use \Mailjet\Resources;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

class MailService
{

    public function send() {
        $mj = new Client($_ENV['MAIL_API_KEY'], $_ENV['MAIL_API_PRIVATE_KEY'] , true, ['version' => 'v3.1']);
        $body = ['Messages' => [['From' => ['Email' => "boutet1406@gmail.com",
            'Name' => "Quentin"],
            'To' => [[
                'Email' => "quentin.boutet2020@campus-eni.fr",
                'Name' => "Quentin"
            ]],
            'Subject' => "Greetings from Mailjet.",
            'TextPart' => "My first Mailjet email",
            'HTMLPart' => "Youyou",
            'CustomID' => "AppGettingStartedTest"]]];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success() && var_dump($response->getData());
    }
}
