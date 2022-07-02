<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Home_model extends MY_Model {
    public function edit_poin(){
        $id = $this->input->post("id");
        $poin = $this->input->post("poin");

        foreach ($id as $i => $id) {
            $this->edit_data("nilai_toefl", ["id" => $id], ["poin" => $poin[$i]]);
        }
    }

    public function getPengaturanAkun(){
        $id_admin = $this->session->userdata("id_admin");
        $data = $this->get_one("admin", ["id_admin" => $id_admin]);
        $config = $this->get_one("config", ["field" => "background"]);
        $data['background'] = $config['value'];
        $config = $this->get_one("config", ["field" => "no_wa"]);
        $data['no_wa'] = $config['value'];
        $config = $this->get_one("config", ["field" => "wa_pretest"]);
        $data['wa_pretest'] = $config['value'];
        $config = $this->get_one("config", ["field" => "wa_progress_test"]);
        $data['wa_progress_test'] = $config['value'];
        $config = $this->get_one("config", ["field" => "wa_post_test"]);
        $data['wa_post_test'] = $config['value'];
        return $data;
    }

    public function edit_pengaturan(){
        $background = $this->input->post("background");
        $no_wa = $this->input->post("no_wa");
        $wa_pretest = $this->input->post("wa_pretest");
        $wa_progress_test = $this->input->post("wa_progress_test");
        $wa_post_test = $this->input->post("wa_post_test");
        unset($_POST['no_wa']);
        unset($_POST['background']);
        unset($_POST['wa_pretest']);
        unset($_POST['wa_progress_test']);
        unset($_POST['wa_post_test']);
        $this->edit_data("config", ["field" => "no_wa"], ["value" => $no_wa]);
        $this->edit_data("config", ["field" => "wa_pretest"], ["value" => $wa_pretest]);
        $this->edit_data("config", ["field" => "wa_progress_test"], ["value" => $wa_progress_test]);
        $this->edit_data("config", ["field" => "wa_post_test"], ["value" => $wa_post_test]);
        $this->edit_data("config", ["field" => "background"], ["value" => $background]);

        $id_admin = $this->input->post("id_admin");
        unset($_POST['id_admin']);

        $data = [];
        foreach ($_POST as $key => $value) {
            if($key == "password"){
                if($this->input->post("password") != ""){
                    $data[$key] = md5($this->input->post($key));
                }
            } else {
                $data[$key] = $this->input->post($key);
            }
        }
        
        if(!empty($data)) $query = $this->edit_data("admin", ["id_admin" => $id_admin], $data);

        return 1;
    }
}

/* End of file Home_model.php */
