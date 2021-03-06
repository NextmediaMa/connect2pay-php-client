<?php
require_once (dirname(__FILE__) . "/../src/Connect2PayClient.php");
require_once (dirname(__FILE__) . "/configuration.php");

use PayXpert\Connect2Pay\Connect2PayClient;

// Merchant token should be passed as the first parameter of this script
// Encrypted status should be passed as the second argument
if ($argc < 3) {
  echo "Usage: php encrypted-status.php merchantToken encryptedstatus\n";
  exit(1);
}

$merchantToken = $argv[1];
$encryptedStatus = $argv[2];

$c2pClient = new Connect2PayClient($connect2pay, $originator, $password);
if ($c2pClient->handleRedirectStatus($encryptedStatus, $merchantToken)) {
  $status = $c2pClient->getStatus();
  if ($status != null && $status->getErrorCode() != null) {
    echo "Merchant token: " . $status->getMerchantToken() . "\n";
    echo "Status: " . $status->getStatus() . "\n";
    echo "Error code: " . $status->getErrorCode() . "\n";

    $transaction = $status->getLastTransactionAttempt();

    if ($transaction !== null) {
      echo "Number of transaction attempts: " . count($status->getTransactions()) . "\n";
      echo "Payment type: " . $transaction->getPaymentType() . "\n";
      echo "Operation: " . $transaction->getOperation() . "\n";

      echo "Error message: " . $transaction->getResultMessage() . "\n";
      echo "Transaction ID: " . $transaction->getTransactionID() . "\n";

      $transactionDate = $transaction->getDateAsDateTime();
      if ($transactionDate !== null) {
        echo "Transaction date: " . $transactionDate->format("Y-m-d H:i:s T") . "\n";
      }

      if ($transaction->getSubscriptionID()) {
        echo "Subscription ID: " . $transaction->getSubscriptionID() . "\n";
      }
      $paymentMeanInfo = $transaction->getPaymentMeanInfo();
      if ($paymentMeanInfo !== null) {
        switch ($transaction->getPaymentType()) {
          case Connect2PayClient::_PAYMENT_TYPE_CREDITCARD:
            if (!empty($paymentMeanInfo->getCardNumber())) {
              echo "Payment Mean Information:\n";
              echo "* Card Holder Name: " . $paymentMeanInfo->getCardHolderName() . "\n";
              echo "* Card Number: " . $paymentMeanInfo->getCardNumber() . "\n";
              echo "* Card Expiration: " . $paymentMeanInfo->getCardExpireMonth() . "/" . $paymentMeanInfo->getCardExpireYear() . "\n";
              echo "* Card Brand: " . $paymentMeanInfo->getCardBrand() . "\n";
              if (!empty($paymentMeanInfo->getCardLevel())) {
                echo "* Card Level/subtype: " . $paymentMeanInfo->getCardLevel() . "/" . $paymentMeanInfo->getCardSubType() . "\n";
                echo "* Card country code: " . $paymentMeanInfo->getIinCountry() . "\n";
                echo "* Card bank name: " . $paymentMeanInfo->getIinBankName() . "\n";
              }
            }
            break;
          case Connect2PayClient::_PAYMENT_TYPE_TODITOCASH:
            if (!empty($paymentMeanInfo->getCardNumber())) {
              echo "Payment Mean Information:\n";
              echo "* Card Number: " . $paymentMeanInfo->getCardNumber() . "\n";
            }
            break;
          case Connect2PayClient::_PAYMENT_TYPE_BANKTRANSFER:
            $sender = $paymentMeanInfo->getSender();
            $recipient = $paymentMeanInfo->getRecipient();
            if ($sender !== null) {
              echo "Payment Mean Information:\n";
              echo "* Sender Account:\n";
              echo ">> Holder Name: " . $sender->getHolderName() . "\n";
              echo ">> Bank Name: " . $sender->getBankName() . "\n";
              echo ">> IBAN: " . $sender->getIban() . "\n";
              echo ">> BIC: " . $sender->getBic() . "\n";
              echo ">> Country code: " . $sender->getCountryCode() . "\n";
            }
            if ($recipient !== null) {
              echo "* Recipient Account:\n";
              echo ">> Holder Name: " . $recipient->getHolderName() . "\n";
              echo ">> Bank Name: " . $recipient->getBankName() . "\n";
              echo ">> IBAN: " . $recipient->getIban() . "\n";
              echo ">> BIC: " . $recipient->getBic() . "\n";
              echo ">> Country code: " . $recipient->getCountryCode() . "\n";
            }
            break;
        }
      }
      if ($status->getCtrlCustomData()) {
        echo "Custom Data: " . $status->getCtrlCustomData() . "\n";
      }
      $shopper = $transaction->getShopper();
      if ($shopper !== null) {
        echo "Shopper info:\n";
        echo "* Name: " . $shopper->getName() . "\n";
        echo "* Address: " . $shopper->getAddress() . " - " . $shopper->getZipcode() . " " . $shopper->getCity() . " - " .
             $shopper->getCountryCode() . "\n";
        echo "* Email: " . $shopper->getEmail() . "\n";
        if (!empty($shopper->getBirthDate())) {
          echo "* Birth date: " . $shopper->getBirthDate() . "\n";
        }
        if (!empty($shopper->getIdNumber())) {
          echo "* ID Number: " . $shopper->getIdNumber() . "\n";
        }
        if (!empty($shopper->getIpAddress())) {
          echo "* IP Address: " . $shopper->getIpAddress() . "\n";
        }
      }
    }
  }
} else {
  echo "Error: " . $c2pClient->getClientErrorMessage() . "\n";
}
?>
