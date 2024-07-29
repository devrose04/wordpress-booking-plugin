<?php

class Guesty_API
{
    private $client_id;
    private $client_secret;
    private $base_url;
    private $access_token;
    private $booking_list = array();

    public function __construct()
    {
        $encrypted_client_id = get_option('guesty_client_id');
        $this->client_id = guesty_decrypt($encrypted_client_id);
        $this->client_secret = guesty_decrypt(get_option('guesty_client_secret'));
        $environment = get_option('guesty_environment');
        $this->access_token = get_option('guesty_access_token');
        $this->base_url = $environment === 'production' ? 'https://booking.guesty.com/' : 'https://booking-sandbox.guesty.com/';
    }

    public function test_connection()
    {
        $response = wp_remote_post($this->base_url . 'oauth2/token', array(
            'headers' => array(
                'accept' => 'application/json',
                'cache-control' => 'no-cache,no-cache',
                'content-type' => 'application/x-www-form-urlencoded',
            ),
            'body' => array(
                'grant_type' => 'client_credentials',
                'scope' => 'booking_engine:api',
                'client_secret' => $this->client_secret,
                'client_id' => $this->client_id,
            ),
        ));

        if (is_wp_error($response)) {
            return $response->get_error_message();
        }

        $body = wp_remote_retrieve_body($response);
        $result_data = json_decode($body, true);
        if (!isset($result_data['access_token'])) {
            return $result_data['error']['message'];
        } else {
            $token = $result_data['access_token'];
            update_option('guesty_access_token', $token);
            $res = $token;
            return "Successfully Connected!";
        }
    }

    public function fetch_guesty_list_data()
    {
        $api_url = $this->base_url . 'api/listings';
        $response = wp_remote_get($api_url, array('headers' => $this->get_headers()));
        if (is_wp_error($response)) {
            return false; // Handle errors accordingly
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Debugging: Output the data to error log
        error_log(print_r($data, true));

        return $data;
    }

    public function fetch_guesty_detail_data($id)
    {
        $api_url = $this->base_url . 'api/listings/' . $id;
        $response = wp_remote_get($api_url, array('headers' => $this->get_headers()));
        if (is_wp_error($response)) {
            return false; // Handle errors accordingly
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Debugging: Output the data to error log
        error_log(print_r($data, true));

        return $data;
    }

    public function fetch_guesty_calendar_data($id, $startDate, $endDate)
    {
        $api_url = $this->base_url . '/api/listings/' . $id . '/calendar?from=' . $startDate . '&to=' . $endDate;
        // return $api_url;
        $response = wp_remote_get($api_url, array('headers' => $this->get_headers()));
        if (is_wp_error($response)) {
            return false; // Handle errors accordingly
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Debugging: Output the data to error log
        error_log(print_r($data, true));

        return $data;
    }

    public function new_booking_data($id, $checkin, $checkout, $count)
    {
        $api_url = $this->base_url . '/api/reservations/quotes';
        $response = wp_remote_post($api_url, array(
            'headers' => $this->get_headers(),
            'body' => json_encode(array(
                'listingId' => $id,
                'guestsCount' => $count,
                'checkInDateLocalized' => $checkin,
                'checkOutDateLocalized' => $checkout
            ))
        ));

        if (is_wp_error($response)) {
            return array(
                'status' => 'error',
                'message' => $response->get_error_message()
            ); // Handle errors accordingly
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_message = wp_remote_retrieve_response_message($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Debugging: Output the data to error log
        error_log(print_r($data, true));

        return array(
            'status' => $response_code == 200 ? 'success' : 'error',
            'message' => $response_message,
            'data' => $data
        );
    }

    public function get_token()
    {
        return 'Bearer ' . $this->access_token;
    }

    private function get_headers()
    {
        return array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->access_token,
        );
    }
}
