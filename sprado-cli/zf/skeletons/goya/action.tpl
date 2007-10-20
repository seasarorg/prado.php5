
    public function @@ACTION_NAME@@() {
        $this->view->assign('dtos', $this->@@SERVICE_PROPERTY@@->getWithLimit(10));
    }
    /** S2BASE_PHP5 ACTION METHOD **/

    private $@@SERVICE_PROPERTY@@;
    public function set@@SERVICE_CLASS@@(@@SERVICE_CLASS@@ $service) {
        $this->@@SERVICE_PROPERTY@@ = $service;
    }