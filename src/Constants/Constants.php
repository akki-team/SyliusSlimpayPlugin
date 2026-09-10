<?php

namespace Akki\SyliusSlimpayPlugin\Constants;


class Constants
{
    const PAYMENT_SCHEME_SEPA_DIRECT_DEBIT_CORE = 'SEPA.DIRECT_DEBIT.CORE';
    const PAYMENT_SCHEME_SEPA_DIRECT_DEBIT_B2B = 'SEPA.DIRECT_DEBIT.B2B';
    const PAYMENT_SCHEME_BACS_DIRECT_DEBIT = 'BACS.DIRECT_DEBIT';
    const PAYMENT_SCHEME_SEPA_CREDIT_TRANSFER = 'SEPA.CREDIT_TRANSFER';
    const PAYMENT_SCHEME_CARD = 'CARD';

    const PAYMENT_STATUS_TO_PROCESS = 'toprocess';
    const PAYMENT_STATUS_PROCESSING = 'processing';
    const PAYMENT_STATUS_NOT_PROCESSED = 'notprocessed';
    const PAYMENT_STATUS_TO_REPLAY = 'toreplay';
    const PAYMENT_STATUS_PROCESSED = 'processed';
    const PAYMENT_STATUS_REJECTED = 'rejected';

    const ORDER_STATE_CLOSED = 'closed';
    const ORDER_STATE_ABORT = 'closed.aborted';
    const ORDER_STATE_ABORT_BY_SERVER = 'closed.aborted.aborted_byserver';
    const ORDER_STATE_ABORT_BY_CLIENT = 'closed.aborted.aborted_byclient';
    const ORDER_STATE_COMPLETE = 'closed.completed';
    const ORDER_STATE_RUNNING = 'open.running';
    const ORDER_STATE_NOT_RUNNING = 'open.not_running';
    const ORDER_STATE_NOT_RUNNING_SUSPENDED = 'open.not_running.suspended';
    const ORDER_STATE_NOT_RUNNING_SUSPENDED_AVAITING_INPUT = 'open.not_running.suspended.awaiting_input';
    const ORDER_STATE_NOT_RUNNING_SUSPENDED_AVAITING_VALIDATION = 'open.not_running.suspended.awaiting_validation';
    const ORDER_STATE_NOT_RUNNING_NOT_STARTED = 'open.not_running.not_started';


    const BASE_URI_SANDBOX = 'https://api.preprod.slimpay.com';
    const BASE_URI_PROD = 'https://api.slimpay.net';
    const PROFILE_URI = 'https://api.slimpay.net';
    const RELATION_URI = 'https://api.slimpay.net';

    const FOLLOW_CREATE_ORDERS = 'create-orders';
    const FOLLOW_CREATE_PAYINS = 'create-payins';
    const FOLLOW_CREATE_PAYOUTS = 'create-payouts';
    const FOLLOW_USER_APPROVAL = 'user-approval';
    const FOLLOW_EXTENDED_USER_APPROVAL = 'extended-user-approval';
    const FOLLOW_GET_MANDATES = 'get-mandates';
    const FOLLOW_GET_MANDATE = 'get-mandate';
    const FOLLOW_GET_CARD_ALIAS = 'get-card-alias';
    const FOLLOW_UPDATE_BANK_ACCOUNT = 'update-bank-account';
    const FOLLOW_SEARCH_PAYMENT_BY_ID = 'search-payment-by-id';
    const FOLLOW_SEARCH_ORDER_BY_ID = 'search-order-by-id';
    const FOLLOW_GET_BANK_ACCOUNT = 'get-bank-account';

    const ITEM_TYPE_SIGN_MANDATE = 'signMandate';
    const ITEM_TYPE_MANDATE = 'mandate';
    const ITEM_TYPE_CARD_ALIAS = 'cardAlias';
    const ITEM_TYPE_PAYMENT = 'payment';

    const ITEM_ACTION_SIGN = 'sign';
    const ITEM_ACTION_AMEND_BANK_ACCOUNT = 'amendBankAccount';
    const ITEM_ACTION_CREATE = 'create';

    const CHECKOUT_MODE_REDIRECT = 'redirect';
    const CHECKOUT_MODE_IFRAME_POPIN = 'iframepopin';
    const CHECKOUT_MODE_IFRAME_EMBADDED = 'iframeembedded';

    /**
     * @return array
     */
    public static function getSupportedPaymentShemas()
    {
        return [
            self::PAYMENT_SCHEME_CARD,
            self::PAYMENT_SCHEME_BACS_DIRECT_DEBIT,
            self::PAYMENT_SCHEME_SEPA_DIRECT_DEBIT_B2B,
            self::PAYMENT_SCHEME_SEPA_DIRECT_DEBIT_CORE
        ];
    }
}