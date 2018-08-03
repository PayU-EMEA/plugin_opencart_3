<?php
class ControllerExtensionPaymentPayU extends Controller {

    const VERSION = '3.3.2';

    private $error = array();
    private $settings = array();

    public function index() {
        $this->load->language('extension/payment/payu');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_payu', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }

        $data['text_info'] = sprintf($this->language->get('text_info'), self::VERSION);

        $data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
        $data['error_signaturekey'] = isset($this->error['signaturekey']) ? $this->error['signaturekey'] : '';
        $data['error_merchantposid'] = isset($this->error['merchantposid']) ? $this->error['merchantposid'] : '';
        $data['error_oauth_client_id'] = isset($this->error['oauth_client_id']) ? $this->error['oauth_client_id'] : '';
        $data['error_oauth_client_secret'] = isset($this->error['oauth_client_secret']) ? $this->error['oauth_client_secret'] : '';

        $this->load->model('localisation/geo_zone');
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $data['payment_payu_total'] = isset($this->request->post['payment_payu_total']) ?
            $this->request->post['payment_payu_total'] : $this->config->get('payment_payu_total');

        $data['payment_payu_geo_zone_id'] = isset($this->request->post['payment_payu_geo_zone_id']) ?
            $this->request->post['payment_payu_geo_zone_id'] : $this->config->get('payment_payu_geo_zone_id');

        $data['payment_payu_signaturekey'] = isset($this->request->post['payment_payu_signaturekey']) ?
            $this->request->post['payment_payu_signaturekey'] : $this->config->get('payment_payu_signaturekey');

        $data['payment_payu_merchantposid'] = isset($this->request->post['payment_payu_merchantposid']) ?
            $this->request->post['payment_payu_merchantposid'] : $this->config->get('payment_payu_merchantposid');

        $data['payment_payu_oauth_client_id'] = isset($this->request->post['payment_payu_oauth_client_id']) ?
            $this->request->post['payment_payu_oauth_client_id'] : $this->config->get('payment_payu_oauth_client_id');

        $data['payment_payu_oauth_client_secret'] = isset($this->request->post['payment_payu_oauth_client_secret']) ?
            $this->request->post['payment_payu_oauth_client_secret'] : $this->config->get('payment_payu_oauth_client_secret');

        $data['payment_payu_status'] = isset($this->request->post['payment_payu_status']) ?
            $this->request->post['payment_payu_status'] : $this->config->get('payment_payu_status');

        $data['payment_payu_sort_order'] = isset($this->request->post['payment_payu_sort_order']) ?
            $this->request->post['payment_payu_sort_order'] :  $this->config->get('payment_payu_sort_order');

        $data['payment_payu_new_status'] = isset($this->request->post['payment_payu_new_status']) ?
            $this->request->post['payment_payu_new_status'] : $this->config->get('payment_payu_new_status');

        $data['payment_payu_cancelled_status'] = isset($this->request->post['payment_payu_cancelled_status']) ?
            $this->request->post['payment_payu_cancelled_status'] : $this->config->get('payment_payu_cancelled_status');

        $data['payment_payu_pending_status'] = isset($this->request->post['payment_payu_pending_status']) ?
            $this->request->post['payment_payu_pending_status'] : $this->config->get('payment_payu_pending_status');

        $data['payment_payu_complete_status'] = isset($this->request->post['payment_payu_complete_status']) ?
            $this->request->post['payment_payu_complete_status'] : $this->config->get('payment_payu_complete_status');

        $data['payment_payu_waiting_for_confirmation_status'] = isset($this->request->post['payment_payu_waiting_for_confirmation_status']) ?
             $this->request->post['payment_payu_waiting_for_confirmation_status']:  $this->config->get('payment_payu_waiting_for_confirmation_status');

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/payu', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/payment/payu', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/payu', $data));

    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/payment/payu')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['payment_payu_signaturekey']) {
            $this->error['signaturekey'] = $this->language->get('error_signaturekey');
        }
        if (!$this->request->post['payment_payu_merchantposid']) {
            $this->error['merchantposid'] = $this->language->get('error_merchantposid');
        }
        if (!$this->request->post['payment_payu_oauth_client_id']) {
            $this->error['oauth_client_id'] = $this->language->get('error_oauth_client_id');
        }
        if (!$this->request->post['payment_payu_oauth_client_secret']) {
            $this->error['oauth_client_secret'] = $this->language->get('error_oauth_client_secret');
        }

        return !$this->error;
    }

    public function install()
    {
        $this->load->model('extension/payment/payu');
        $this->load->model('setting/setting');

        $this->settings = array(
            'payment_payu_new_status' => 1,
            'payment_payu_pending_status' => 1,
            'payment_payu_complete_status' => 2,
            'payment_payu_cancelled_status' => 7,
            'payment_payu_waiting_for_confirmation_status' => 1,
            'payment_payu_geo_zone_id' => 0,
            'payment_payu_sort_order' => 1,
        );
        $this->model_setting_setting->editSetting('payment_payu', $this->settings);
        $this->model_extension_payment_payu->createDatabaseTables();
    }

    public function uninstall()
    {
        $this->load->model('extension/payment/payu');
        $this->load->model('setting/setting');

        $this->model_setting_setting->deleteSetting('payment_payu');
        $this->model_extension_payment_payu->dropDatabaseTables();
    }

}