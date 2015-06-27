<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('mysqlimodel');
        $GLOBALS['$mysqli'] = $this->mysqlimodel->initialise();

        $this->load->model('basemodel');
        if($this->basemodel->check_db()){
            $this->load->model('categories');
            $this->load->model('xls');

            $data['table_name'] = $this->basemodel->get_table_name();
            $data['input_selects'] = $this->basemodel->get_input_select();

            $this->load->view('header', $data);
        }
    }

    public function index()
    {
        if(!$this->basemodel->check_db()){
            header("Location: ./create_db");
            die();
        }

        if(isset($_FILES[key($_FILES)]['size']) && $_FILES[key($_FILES)]['size'] > 0){
            $xls_array = $this->xls->get_array();

            $this->basemodel->create_table();
            $this->basemodel->insert($xls_array);
        }

        $data['tables'] = $this->basemodel->show_tables("select");
        $this->load->view('show', $data);
    }

    public function show(){
        if($this->uri->segment(2)){
            $data['base_table'] = $this->basemodel->show_table(false, "base", $this->uri->segment(2));
            $this->load->view('welcome_message', $data);
        }else{
            $data['tables'] = $this->basemodel->show_tables("select");
            $this->load->view('show', $data);
        }
    }

    public function create_db(){
        $this->basemodel->create_db();
    }

    public function compare(){
        $data['compare_table'] = $this->basemodel->compare(strpos($this->uri->segment(2), "-")?$this->uri->segment(2):false);
        $this->load->view('compare', $data);
    }

    public function new_products(){
        $data['new_positions'] = $this->basemodel->new_products(strpos($this->uri->segment(2), "-")?$this->uri->segment(2):false);
        $this->load->view('compare', $data);
    }

    public function missed_products(){
        $data['zero_positions'] = $this->basemodel->zero_products(strpos($this->uri->segment(2), "-")?$this->uri->segment(2):false);
        $this->load->view('compare', $data);
    }

    public function prices(){
        $this->load->model('prices');
        //        $this->prices->parse();
        $data['categories'] = $this->prices->get_categories_total();
        $data['competitors'] = $this->prices->get_competitors();
        $data['table'] = $this->prices->get_table();
        $this->load->view('prices', $data);
    }

    public function refresh(){
        $this->load->model('prices');
        $this->prices->refresh();
    }

    public function delete(){
        if($this->uri->segment(2)!="")
            $this->basemodel->delete_table($this->uri->segment(2));

        $data['tables'] = $this->basemodel->show_tables("delete");

        $this->load->view('delete', $data);
    }

    public function ajax(){
        $this->load->model('ajax');
    }

}
