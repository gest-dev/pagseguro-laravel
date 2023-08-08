<?php

namespace Oliveiracorp\PagSeguro;

class PagSeguroPix extends PagSeguroClient
{

    /**
     * Informações do comprador.
     *
     * @var array
     */
    private $customer = [];
    /**
     * Identificador da compra.
     *
     * @var string
     */
    private $reference;

    /**
     * Data de vencimento do pix.
     *
     * @var string
     */
    private $firstDueDate;

    /**
     * Quantidade de pixs gerados.
     *
     * @var string
     */
    private $numberOfPayments;

    /**
     * Valor da compra.
     *
     * @var string
     */
    private $amount;

    /**
     * Instruções do pix.
     *
     * @var string
     */
    private $instructions;

    /**
     * Descrição do item do pix.
     *
     * @var string
     */
    private $description;

    /**
     * itens do comprador.
     *
     * @var array
     */
    private $items = [];
    /**
     * Número de Itens da compra.
     *
     * @var int
     */
    private $itemsCount = 0;

    /**
     * Endereço do comprador.
     *
     * @var array
     */
    private $shippingAddress = [];

    /**
     * Define um id de referência da compra no pagseguro.
     *
     * @param string $reference
     *
     * @return $this
     */
    public function setReference($reference)
    {
        $this->reference = $this->sanitize($reference);

        return $this;
    }

    protected function sanitizeMoneyPagSeguro($value, $key = null)
    {
        $value = $this->checkValue($value, $key);

        return $value == null ? $value : str_replace('.', '', $value);
    }

    /**
     * Define o valor da compra no pagseguro.
     *
     * @param string $amount
     *
     * @return $this
     */
    public function setAmount($amount)
    {

        $this->amount = $this->sanitizeMoneyPagSeguro($amount);

        return $this;
    }

    /**
     * Define o vendimento do pix da compra no pagseguro.
     *
     * @param \Carbon\Carbon $firstDueDate
     *
     * @return $this
     */
    public function setFirstDueDate($firstDueDate)
    {

        $this->firstDueDate = $firstDueDate->format('Y-m-d\TH:i:sP');

        return $this;
    }

    /**
     * Define a quantidade de pix gerados no pagseguro.
     *
     * @param string $numberOfPayments
     *
     * @return $this
     */
    public function setNumberOfPayments($numberOfPayments)
    {
        $this->numberOfPayments = $this->sanitizeNumber($numberOfPayments);

        return $this;
    }

    /**
     * Define a do item do pix no pagseguro.
     *
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $this->sanitize($description);

        return $this;
    }

    /**
     * Define a instrução do pix no pagseguro.
     *
     * @param string $instructions
     *
     * @return $this
     */
    public function setInstructions($instructions)
    {
        $this->instructions = $this->sanitize($instructions);

        return $this;
    }

    /**
     * Define os dados do comprador.
     *
     * @param array $customerInfo
     *
     * @throws \Oliveiracorp\PagSeguro\PagSeguroException
     *
     * @return PagSeguroPix
     */
    public function setCustomerInfo(array $customerInfo)
    {
        $customerEmail = $this->sanitize($customerInfo, 'email');

        $this->customer = [
            'name' => $this->sanitize($customerInfo, 'name'),
            'email' => $customerEmail,
            'tax_id' => $this->sanitizeNumber($customerInfo, 'tax_id'),
        ];
        $this->validateCustomerInfo($this->customer);

        return $this;
    }

    /**
     * Valida os dados contidos na array de informações do comprador.
     *
     * @param array $customerInfo
     *
     * @throws \Oliveiracorp\PagSeguro\PagSeguroException
     */
    private function validateCustomerInfo(array $customerInfo)
    {

        $rules = [
            'name' => 'required|max:50',
            'email' => 'required|email|max:60',
            'tax_id' => 'required|digits:11',
        ];

        $this->validate($customerInfo, $rules);

    }

    /**
     * Define os itens da compra.
     *
     * @param array $items
     *
     * @return $this
     */
    public function setItems(array $items)
    {

        $cont = 0;
        $fItems = array();
        foreach ($items as $item) {

            $cont++;
            $fItems = array_merge($fItems, [[
                'name' => $this->sanitize($item, 'name'),
                'unit_amount' => $this->sanitizeMoneyPagSeguro($item, 'unit_amount'),
                'quantity' => $this->sanitizeNumber($item, 'quantity'),
            ]]);
        }
        $this->itemsCount = $cont;
        $this->validateItems($fItems);
        $this->items = $fItems;

        return $this;
    }

    /**
     * Valida os dados contidos na array de itens.
     *
     * @param array $items
     */
    private function validateItems($items)
    {
        foreach ($items as $key => $value) {

            $this->validate($value, [
                'name' => 'required|max:100',
                'unit_amount' => 'required|numeric|between:0.00,9999999.00',
                'quantity' => 'required|integer|between:1,999',
            ]);
        }
    }

    /**
     * Define o endereço do comprador.
     *
     * @param array $shippingAddress
     *
     * @return $this
     */
    public function setCustomerAddress(array $shippingAddress)
    {
        $shippingAddress = [
            'street' => $this->sanitize($shippingAddress, 'street'),
            'number' => $this->sanitize($shippingAddress, 'number'),
            'complement' => $this->sanitize($shippingAddress, 'complement'),
            'locality' => $this->sanitize($shippingAddress, 'locality'),
            'postal_code' => $this->sanitizeNumber($shippingAddress, 'postal_code'),
            'city' => $this->sanitize($shippingAddress, 'city'),
            'region_code' => strtoupper($this->checkValue($shippingAddress, 'region_code')),
            'country' => 'BRA',
        ];

        $this->validateCustomerAddress($shippingAddress);
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    /**
     * Valida os dados contidos na array de endereço do comprador.
     *
     * @param array $shippingAddress
     */
    private function validateCustomerAddress(array $shippingAddress)
    {

        $rules = [
            'street' => 'required|max:80',
            'number' => 'required|max:20',
            'complement' => 'max:40',
            'locality' => 'required|max:60',
            'postal_code' => 'required|digits:8',
            'city' => 'required|min:2|max:60',
            'region_code' => 'required|min:2|max:2',
        ];

        $this->validate($shippingAddress, $rules);
    }

    /**
     * Define o url de notificação.
     *
     * @param array $url_notification
     *
     * @return $this
     */
    public function setNotificationUrl(string $url_notification)
    {
       
        $urlArray = [
            'url' => $url_notification,
        ];

        $this->validateNotificationUrl($urlArray);
        $this->notificationURL = $url_notification;

        return $this;
    }

    /**
     * Valida os dados contidos na array de notificação.
     *
     * @param array $url_notification
     */
    private function validateNotificationUrl(array $url_notification)
    {

        $rules = [
            'url' => 'required|url',
        ];

        $this->validate($url_notification, $rules);
    }

    /**
     * Envia o pix para o pagseguro.
     *
     * @throws \Oliveiracorp\PagSeguro\PagSeguroException
     *
     * @return \SimpleXMLElement
     */
    public function sendPix()
    {
        if (empty($this->firstDueDate)) {
            self::setFirstDueDate(\Carbon\Carbon::now()->addDays(3));
        }

        $this->validatePaymentSettings();

        $config = [
            'reference_id' => $this->reference,
            'customer' => $this->customer,
            'qr_codes' => array([
                'amount' => [
                    "value" => $this->amount,
                ],
                'expiration_date' => $this->firstDueDate,
            ]),
            'shipping' => [
                'address' => $this->shippingAddress,
            ],
            'items' => $this->items,
            'notification_urls' => [$this->notificationURL],

        ];

        return $this->sendJsonTransaction($config, $this->url['pix'], 'POST', [
            'Accept: application/json',
            'Content-type: application/json',
            'Authorization : Bearer ' . $this->token . '']);
    }

    /**
     * Valida os dados de pagamento.
     *
     * @throws \Oliveiracorp\PagSeguro\PagSeguroException
     */
    private function validatePaymentSettings()
    {

        $this->validateCustomerInfo($this->customer);
        $this->validateCustomerAddress($this->shippingAddress);
    }

}
