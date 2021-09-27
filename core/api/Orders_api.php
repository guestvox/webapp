<?php

class Orders_api extends Model
{
    public function get($params)
    {
        if (!empty($params[0]))
        {
            if (!empty($params[1]))
            {
                $query = Functions::get_json_decoded_query($this->database->select('menu_orders', '*', [
                    'id' => $params[1],
                    'ORDER' => [
                        'id' => 'DESC'
                    ]
                ]));

                return !empty($query) ? $query[0] : 'No se encontraron registros';
            }
            else
            {
                $query = Functions::get_json_decoded_query($this->database->select('menu_orders', '*', [
                    'account' => $params[0]
                ]));

                return !empty($query) ? $query : 'No se encontraron registros';
            }
        }
        else
            return 'Cuenta no establecida';
    }

    public function post($params)
    {
        if ($_POST['status'] == 'declined')
        {
            $query = $this->database->update('menu_orders', [
                'declined' => true
            ], [
                'id' => $_POST['id']
            ]);

            return !empty($query) ? 'Orden declinada' : 'Error de operación';
        }
        else if ($_POST['status'] == 'accepted')
        {
            $query = $this->database->update('menu_orders', [
                'accepted' => true
            ], [
                'id' => $_POST['id']
            ]);

            return !empty($query) ? 'Orden aceptada' : 'Error de operación';
        }
        else if ($_POST['status'] == 'delivered')
        {
            $query = $this->database->update('menu_orders', [
                'delivered' => true
            ], [
                'id' => $_POST['id']
            ]);

            return !empty($query) ? 'Orden entregada' : 'Error de operación';
        }
        else if ($_POST['status'] == 'accepted_and_delivered')
        {
            $query = $this->database->update('menu_orders', [
                'accepted' => true,
                'delivered' => true
            ], [
                'id' => $_POST['id']
            ]);

            return !empty($query) ? 'Orden aceptada y terminada' : 'Error de operación';
        }
    }

    public function put($params)
    {
        return 'Ok';
    }

    public function delete($params)
    {
        return 'Ok';
    }
}
