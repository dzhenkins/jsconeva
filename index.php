<!DOCTYPE html>
<html>
<head>
    <title>Перевірка JSON</title>
</head>
<body>
    <form method="post">
        <label for="json_input">JSON:</label><br>
        <textarea id="json_input" name="json_input" rows="10" cols="50"></textarea><br>
        <input type="submit" value="Перевірити">
    </form>
    <?php
    // Перевірка, чи було надіслано JSON
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['json_input'])) {
            // Отримуємо JSON з форми
            $json = $_POST['json_input'];
            // Перетворюємо JSON у масив
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_IGNORE);
            
            // Поля, які потрібно перевірити в кореневому рівні JSON
            $fields_to_check_root = [
                "customer_id",
                "customer_email",
                "grand_total",
                "store_id",
                "store_name",
                "increment_id",
                "entity_id",
                "relation_parent_real_id",
                "original_increment_id",
                "shipping_amount",
                "weight",
                "state",
                "status",
                "operation_id",
                "merchant_id",
                "merchant_name",
                "merchant_email",
                "canceling_reason_id",
                "new_return_date",
                "ttn_number",
                "user_canceled"
            ];
            
            // Виведення результатів перевірки кореневих полів
            echo "<h2>Кореневі поля:</h2>";
            foreach ($fields_to_check_root as $field) {
                if (array_key_exists($field, $data)) {
                    echo "$field: <span style='color:green;'>присутнє</span><br>";
                } else {
                    echo "$field: <span style='color:red;'>відсутнє</span><br>";
                }
            }

            // Перевірка полів у масиві "items"
            echo "<h2>Блок 'items':</h2>";
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $index => $item) {
                    echo "<h3>Товар $index:</h3>";
                    $fields_to_check_item = [
                        "sku",
                        "name",
                        "price",
                        "qty_ordered",
                        "applied_rule_ids",
                        "row_weight",
                        "original_price",
                        "discount_amount",
                        "row_total",
                        "product_type",
                        "parent_items"
                    ];
                    foreach ($fields_to_check_item as $field) {
                        if (array_key_exists($field, $item)) {
                            echo "$field: <span style='color:green;'>присутнє</span><br>";
                        } else {
                            echo "$field: <span style='color:red;'>відсутнє</span><br>";
                        }
                    }
                    
                    // Перевірка полів в об'єкті "extension_attributes" в товарах
                    echo "<h4>Розширені атрибути:</h4>";
                    if (isset($item['extension_attributes']) && is_array($item['extension_attributes'])) {
                        foreach ($item['extension_attributes'] as $attribute => $value) {
                            echo "<strong>$attribute:</strong><br>";
                            if (is_array($value)) {
                                foreach ($value as $sub_attribute => $sub_value) {
                                    if (is_array($sub_value)) {
                                        foreach ($sub_value as $sub_sub_attribute => $sub_sub_value) {
                                            echo "&emsp;$sub_sub_attribute: $sub_sub_value<br>";
                                        }
                                    } else {
                                        echo "&emsp;$sub_attribute: $sub_value<br>";
                                    }
                                }
                            } else {
                                echo "&emsp;$value<br>";
                            }
                        }
                    } else {
                        echo "Об'єкт 'extension_attributes' не знайдено або не є масивом.";
                    }
                }
            } else {
                echo "Масив 'items' у JSON не знайдено або не є масивом.";
            }

            // Перевірка полів у блоку "billing_address"
            echo "<h2>Блок 'billing_address':</h2>";
            if (isset($data['billing_address']) && is_array($data['billing_address'])) {
                $fields_to_check_billing = [
                    "telephone",
                    "firstname",
                    "lastname",
                    "middlename"
                ];
                foreach ($fields_to_check_billing as $field) {
                    if (array_key_exists($field, $data['billing_address'])) {
                        echo "$field: <span style='color:green;'>присутнє</span><br>";
                    } else {
                        echo "$field: <span style='color:red;'>відсутнє</span><br>";
                    }
                }
            } else {
                echo "Блок 'billing_address' у JSON не знайдено або не є масивом.";
            }

            // Перевірка полів у масиві "payment"
            echo "<h2>Блок 'payment':</h2>";
            if (isset($data['payment']) && is_array($data['payment'])) {
                foreach ($data['payment'] as $index => $payment) {
                    echo "<h3>Метод оплати № $index:</h3>";
                    $fields_to_check_payment = [
                        "method",
                        "amount_ordered",
                        "amount_pay",
                        "giftcertificate_id",
                        "last_trans_id"
                    ];
                    foreach ($fields_to_check_payment as $field) {
                        if (array_key_exists($field, $payment)) {
                            echo "$field: <span style='color:green;'>присутнє</span><br>";
                        } else {
                            echo "$field: <span style='color:red;'>відсутнє</span><br>";
                        }
                    }

                    // Перевірка полів в об'єкті "payment_details_info"
                    echo "<h4>Платіжні деталі:</h4>";
                    if (isset($payment['payment_details_info']) && is_array($payment['payment_details_info'])) {
                        $fields_to_check_payment_details = [
                            "amount",
                            "cardType",
                            "orderReference"
                        ];
                        foreach ($fields_to_check_payment_details as $field) {
                            if (array_key_exists($field, $payment['payment_details_info'])) {
                                echo "$field: <span style='color:green;'>присутнє</span><br>";
                            } else {
                                echo "$field: <span style='color:red;'>відсутнє</span><br>";
                            }
                        }
                    } else {
                        echo "Об'єкт 'payment_details_info' не знайдено або не є масивом.";
                    }
                }
            } else {
                echo "Масив 'payment' у JSON не знайдено або не є масивом.";
            }

            // Перевірка полів у блоку доставки ЦЕ ПИЗДЕЦЬ!!!
            echo "<h2>Блок 'shipping_assignments':</h2>";
            if (isset($data['extension_attributes']) && is_array($data['extension_attributes']) && isset($data['extension_attributes']['shipping_assignments']) && is_array($data['extension_attributes']['shipping_assignments'])) {
                foreach ($data['extension_attributes']['shipping_assignments'] as $index => $assignment) {
                    if (isset($assignment['shipping']) && is_array($assignment['shipping'])) {
                        echo "<h3>Доставка $index:</h3>";
                        $fields_to_check_shipping = [
                            "method",
                            "address"
                        ];
                        foreach ($fields_to_check_shipping as $field) {
                            if (array_key_exists($field, $assignment['shipping'])) {
                                echo "$field: <span style='color:green;'>присутнє</span><br>";
                            } else {
                                echo "$field: <span style='color:red;'>відсутнє</span><br>";
                            }
                        }
                        
                        // Перевірка полів у блоку "address"
                        if (isset($assignment['shipping']['address']) && is_array($assignment['shipping']['address'])) {
                            echo "<h4>Блок 'address':</h4>";
                            $fields_to_check_address = [
                                "address_type",
                                "city",
                                "street"
                            ];
                            foreach ($fields_to_check_address as $field) {
                                if (array_key_exists($field, $assignment['shipping']['address'])) {
                                    echo "$field: <span style='color:green;'>присутнє</span><br>";
                                } else {
                                    echo "$field: <span style='color:red;'>відсутнє</span><br>";
                                }
                            }
                            
                            // Перевірка полів у блоку "extension_attributes" в "address"
                            if (isset($assignment['shipping']['address']['extension_attributes']) && is_array($assignment['shipping']['address']['extension_attributes'])) {
                                echo "<h5>Блок 'extension_attributes' в 'address':</h5>";
                                $fields_to_check_address_extension = [
                                    "method",
                                    "city",
                                    "city_id",
                                    "region",
                                    "region_id",
                                    "warehouse_id",
                                    "warehouse_number",
                                    "comment",
                                    "street_id",
                                    "street"
                                ];
                                foreach ($fields_to_check_address_extension as $field) {
                                    if ($field === 'street' && isset($assignment['shipping']['address']['extension_attributes']['street']) && is_array($assignment['shipping']['address']['extension_attributes']['street'])) {
                                        echo "<h6>Блок 'street' в 'extension_attributes' в 'address':</h6>";
                                        $fields_to_check_street = [
                                            "name",
                                            "house_number",
                                            "apt_number",
                                            "floor",
                                            "elevator"
                                        ];
                                        foreach ($fields_to_check_street as $street_field) {
                                            if (array_key_exists($street_field, $assignment['shipping']['address']['extension_attributes']['street'])) {
                                                echo "$street_field: <span style='color:green;'>присутнє</span><br>";
                                            } else {
                                                echo "$street_field: <span style='color:red;'>відсутнє</span><br>";
                                            }
                                        }
                                    } elseif (array_key_exists($field, $assignment['shipping']['address']['extension_attributes'])) {
                                        echo "$field: <span style='color:green;'>присутнє</span><br>";
                                    } else {
                                        echo "$field: <span style='color:red;'>відсутнє</span><br>";
                                    }
                                }
                            } else {
                                echo "Блок 'extension_attributes' в 'address' не знайдено або не є масивом.";
                            }
                        } else {
                            echo "Блок 'address' в 'shipping' не знайдено або не є масивом.";
                        }
                    } else {
                        echo "Блок 'shipping' в 'shipping_assignments' не знайдено або не є масивом.";
                    }
                }
            } else {
                echo "Блок 'shipping_assignments' у JSON не знайдено або не є масивом.";
            }
        } else {
            echo "Поле 'json_input' не було знайдено в POST-запиті.";
        }
    }
    ?>
</body>
</html>