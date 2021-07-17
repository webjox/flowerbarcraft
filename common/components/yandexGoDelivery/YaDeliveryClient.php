<?php

namespace common\components\yandexGoDelivery;

use yii\base\InvalidConfigException;
use yii\httpclient\Client;
use yii\httpclient\Exception;

/**
 * Class YaDeliveryClient
 * @package common\components\yandexGoDelivery
 */
class YaDeliveryClient
{
    const URL = 'https://b2b.taxi.yandex.net';
    const TOKEN = 'AgAAAABJssy_AAVM1e9rH2FpxEeQmlFY1_Vu0uo';

    /**
     * @return Client
     */
    public static function getClient()
    {
        return new Client([
            'baseUrl' => self::URL,
            'requestConfig' => [
                'format' => Client::FORMAT_JSON
            ],
            'responseConfig' => [
                'format' => Client::FORMAT_JSON
            ],
        ]);
    }

    /**
     * @param $data
     * @return array
     * @throws InvalidConfigException
     * @throws Exception
     */
    public static function sendDeliveryRequest($data)
    {
        $client = self::getClient();

        $response = $client->createRequest()
            ->setFormat(Client::FORMAT_JSON)
            ->setHeaders([
                'Authorization' => 'Bearer ' . self::TOKEN,
                'Accept-Language' => 'ru-RU',
            ])
            ->setMethod('POST')
            ->setUrl('/b2b/cargo/integration/v2/claims/create?request_id=' . uniqid())
            ->setData([
                'client_requirements' => [
                    'taxi_class' => 'express',
                ],
                'items' => $data['items'],
                'route_points' => $data['points'],
                'comment' => $data['comment'],
            ])
            ->send();

        return [
            'statusCode' => $response->getStatusCode(),
            'content' => json_decode($response->getContent(), true),
        ];
    }

    /**
     * @param $claimId
     * @return array
     * @throws Exception
     * @throws InvalidConfigException
     */
    public static function getStatus($claimId)
    {
        $client = self::getClient();

        $response = $client->createRequest()
            ->setFormat(Client::FORMAT_JSON)
            ->setHeaders([
                'Authorization' => 'Bearer ' . self::TOKEN,
                'Accept-Language' => 'ru-RU',
            ])
            ->setMethod('POST')
            ->setUrl("/b2b/cargo/integration/v2/claims/info?claim_id={$claimId}")
            ->send();

        return [
            'statusCode' => $response->getStatusCode(),
            'content' => json_decode($response->getContent(), true),
        ];
    }

    /**
     * @param $claimId
     * @return array
     * @throws Exception
     * @throws InvalidConfigException
     */
    public static function cancelDelivery($claimId)
    {
        $info = self::getStatus($claimId);
        $cancelState = null;
        if ($info['statusCode'] == 200 && !empty($info['content']['available_cancel_state'])) {
            $cancelState = $info['content']['available_cancel_state'];
        }

        if ($cancelState === null) {
            return [
                'statusCode' => 400,
                'content' => ['message' => 'Не удалось получить варианты отмены заявки'],
            ];
        }

        $client = self::getClient();

        $response = $client->createRequest()
            ->setFormat(Client::FORMAT_JSON)
            ->setHeaders([
                'Authorization' => 'Bearer ' . self::TOKEN,
                'Accept-Language' => 'ru-RU',
            ])
            ->setMethod('POST')
            ->setUrl("/b2b/cargo/integration/v1/claims/cancel?claim_id={$claimId}")
            ->setData([
                'cancel_state' => $cancelState,
                'version' => 1,
            ])
            ->send();

        return [
            'statusCode' => $response->getStatusCode(),
            'content' => json_decode($response->getContent(), true),
        ];
    }

    /**
     * @param $claimId
     * @return array
     * @throws Exception
     * @throws InvalidConfigException
     */
    public static function acceptDelivery($claimId)
    {
        $client = self::getClient();

        $response = $client->createRequest()
            ->setFormat(Client::FORMAT_JSON)
            ->setHeaders([
                'Authorization' => 'Bearer ' . self::TOKEN,
                'Accept-Language' => 'ru-RU',
            ])
            ->setMethod('POST')
            ->setUrl("/b2b/cargo/integration/v1/claims/accept?claim_id={$claimId}")
            ->setData([
                'version' => 1,
            ])
            ->send();

        return [
            'statusCode' => $response->getStatusCode(),
            'content' => json_decode($response->getContent(), true),
        ];
    }

    /**
     * @param $status
     * @return string|null
     */
    public static function getStatusInfo($status)
    {
        switch ($status) {
            case 'new':
                return 'Заявка создана';
            case 'estimating':
                return 'Идет процесс оценки заявки';
            case 'estimating_failed':
                return 'Не удалось оценить заявку';
            case 'ready_for_approval':
                return 'Заявка успешно оценена и ожидает подтверждения';
            case 'accepted':
                return 'Заявка подтверждена';
            case 'performer_lookup':
                return 'Заявка взята в обработку';
            case 'performer_draft':
                return 'Идет поиск водителя';
            case 'performer_found':
                return 'Водитель найден и едет за заказом';
            case 'performer_not_found':
                return 'Не удалось найти водителя. Можно попробовать снова через некоторое время';
            case 'pickup_arrived':
                return 'Водитель приехал за заказом';
            case 'ready_for_pickup_confirmation':
                return 'Водитель ждет, когда отправитель назовет ему код подтверждения';
            case 'pickuped':
                return 'Водитель успешно забрал заказ';
            case 'pay_waiting':
                return 'Заказ ожидает оплаты';
            case 'delivery_arrived':
                return 'Водитель приехал к получателю';
            case 'ready_for_delivery_confirmation':
                return 'Водитель ждет, когда получатель назовет ему код подтверждения';
            case 'delivered':
                return 'Водитель успешно доставил заказ';
            case 'delivered_finish':
                return 'Заказ завершен';
            case 'returning':
                return 'Водителю пришлось вернуть заказ и он едет в точку возврата';
            case 'return_arrived':
                return 'Водитель приехал в точку возврата';
            case 'ready_for_return_confirmation':
                return 'Водитель в точке возврата ожидает, когда ему назовут код подтверждения';
            case 'returned':
                return 'Водитель успешно вернул заказ';
            case 'returned_finish':
                return 'Возврат заказа завершен';
            case 'cancelled':
                return 'Заказ был отменен клиентом бесплатно';
            case 'cancelled_with_payment':
                return 'Заказ был отменен клиентом платно (водитель уже приехал)';
            case 'cancelled_by_taxi':
                return 'Водитель отменил заказ (до получения заказа)';
            case 'cancelled_with_items_on_hands':
                return 'Клиент платно отменил заявку без необходимости возврата заказа';
            case 'failed':
                return 'При выполнение заказа произошла ошибка, дальнейшее выполнение невозможно';
        }

        return null;
    }
}
