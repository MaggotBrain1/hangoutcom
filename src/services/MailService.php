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
            'Name' => "Hangout.com"],
            'To' => [[
                'Email' => "quentin.boutet2020@campus-eni.fr",
                'Name' => "Quentin"
            ]],
            'Subject' => "Hangout.com",
            'TextPart' => "Sortie annulée",
            'HTMLPart' => "
<h2>La sortie pour laquelle vous étiez inscrit à été annulée :'(</h2>
<p>nous espérons que vous trouverez votre bonheur à votre prochaine sortie ..</p>
<p>Toutes la team Hangout est avec vous et vous propose pleins autres sorties,</p>
<p>n'hésitez pas à revenir nous voir :)</p>
<br>
<p>La Team Hangout</p>


",
            'CustomID' => "AppGettingStartedTest"]]];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success() && var_dump($response->getData());
    }
}
