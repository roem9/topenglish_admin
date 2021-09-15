<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
defined('BASEPATH') OR exit('No direct script access allowed');

class Tes extends MY_Controller {
    public function index(){
        // navbar and sidebar
        $data['menu'] = "Tes";

        // for title and header 
        $data['title'] = "List Tes";

        // for modal 
        $data['modal'] = [
            "modal_tes",
            "modal_setting"
        ];
        
        // javascript 
        $data['js'] = [
            "ajax.js",
            "function.js",
            "helper.js",
            "modules/setting.js",
            "load_data/tes_reload.js",
            "modules/tes.js",
        ];

        $listSoal = $this->tes->get_all("soal", ["hapus" => 0], "nama_soal");
        foreach ($listSoal as $i => $list) {
            $data['listSoal'][$i] = $list;
            $data['listSoal'][$i]['soal'] = jum_soal($list['id_soal']);
        }

        $this->load->view("pages/tes/list", $data);
    }

    public function hasil($id){
        $tes = $this->tes->get_one("tes", ["md5(id_tes)" => $id]);
        $soal = $this->tes->get_one("soal", ["id_soal" => $tes['id_soal']]);

        $data['tipe'] = $soal['tipe_soal'];
        $data['menu'] = "Hasil";
        $data['id'] = $id;

        // for title and header 
        $data['title'] = "Hasil ".$tes['nama_tes'];

        // for modal 
        $data['modal'] = [
            "modal_hasil_tes",
            "modal_setting"
        ];
        
        // javascript 
        $data['js'] = [
            "ajax.js",
            "function.js",
            "helper.js",
            "modules/setting.js",
            "load_data/hasil_tes_toefl_reload.js",
            "modules/hasil_tes_toefl.js",
        ];

        if($soal['tipe_soal'] == "TOAFL" || $soal['tipe_soal'] == "TOEFL"){
            $this->load->view("pages/tes/list-hasil-toefl", $data);
        } else {
            $this->load->view("pages/tes/list-hasil-latihan", $data);
        }
    }

    // excel
        public function export($file, $id_tes){
            $tes = $this->tes->get_one("tes", ["md5(id_tes)" => $id_tes]);
            $tahun = date('y', strtotime($tes['tgl_tes']));
            $soal = $this->tes->get_one("soal", ["id_soal" => $tes['id_soal']]);
            
            $spreadsheet = new Spreadsheet;

            if($soal){
                if($soal['tipe_soal'] == "TOAFL" || $soal['tipe_soal'] == "TOEFL"){

                    if($file == "hasil"){
                        $semua_peserta = $this->tes->get_all("peserta_toefl", ["id_tes" => $tes['id_tes']]);
                        $file_data = "Hasil Keseluruhan";
                    } else if($file == "sertifikat"){
                        $semua_peserta = $this->tes->get_all("peserta_toefl", ["id_tes" => $tes['id_tes'], "no_doc <> " => ""], "(no_doc + 0)");
                        $file_data = "Sertifikat";
                    }
        
                    $spreadsheet->setActiveSheetIndex(0)
                                ->setCellValue('A1', 'LIST PESERTA ' . $tes['nama_tes'])
                                ->setCellValue('A2', 'No')
                                ->setCellValue('B2', 'No. Sertifikat')
                                ->setCellValue('C2', 'Nama')
                                ->setCellValue('D2', 'TTL')
                                ->setCellValue('E2', 'Alamat')
                                ->setCellValue('F2', 'No. WA')
                                ->setCellValue('G2', 'email')
                                ->setCellValue('H2', 'Nilai Listening')
                                ->setCellValue('H3', 'Benar')
                                ->setCellValue('I3', 'Skor')
                                ->setCellValue('J2', 'Nilai Structure')
                                ->setCellValue('J3', 'Benar')
                                ->setCellValue('K3', 'Skor')
                                ->setCellValue('L2', 'Nilai Reading')
                                ->setCellValue('L3', 'Benar')
                                ->setCellValue('M3', 'Skor')
                                ->setCellValue('N2', 'SKOR TOEFL');
    
                    $spreadsheet->getActiveSheet()->mergeCells('A2:A3')
                                ->mergeCells('B2:B3')
                                ->mergeCells('C2:C3')
                                ->mergeCells('D2:D3')
                                ->mergeCells('E2:E3')
                                ->mergeCells('F2:F3')
                                ->mergeCells('G2:G3')
                                ->mergeCells('H2:I2')
                                ->mergeCells('J2:K2')
                                ->mergeCells('L2:M2')
                                ->mergeCells('N2:N3')
                                ->mergeCells('A1:N1');
                    
                    $kolom = 4;
                    $nomor = 1;
                    foreach($semua_peserta as $peserta) {

                        if($peserta['no_doc'] != "") $no_doc = "{$tahun}/{$peserta['no_doc']}";
                        else $no_doc = "-";

                            $spreadsheet->setActiveSheetIndex(0)
                                        ->setCellValue('A' . $kolom, $nomor)
                                        ->setCellValue('B' . $kolom, $no_doc)
                                        ->setCellValue('C' . $kolom, $peserta['nama'])
                                        ->setCellValue('D' . $kolom, $peserta['t4_lahir'] . ", " . tgl_indo($peserta['tgl_lahir']))
                                        ->setCellValue('E' . $kolom, $peserta['alamat'])
                                        ->setCellValue('F' . $kolom, $peserta['no_wa'])
                                        ->setCellValue('G' . $kolom, $peserta['email'])
                                        ->setCellValue('H' . $kolom, $peserta['nilai_listening'])
                                        ->setCellValue('I' . $kolom, poin("Listening", $peserta['nilai_listening']))
                                        ->setCellValue('J' . $kolom, $peserta['nilai_structure'])
                                        ->setCellValue('K' . $kolom, poin("Structure", $peserta['nilai_structure']))
                                        ->setCellValue('L' . $kolom, $peserta['nilai_reading'])
                                        ->setCellValue('M' . $kolom, poin("Reading", $peserta['nilai_reading']))
                                        ->setCellValue('N' . $kolom, skor($peserta['nilai_listening'], $peserta['nilai_structure'], $peserta['nilai_reading']));
            
                            $kolom++;
                            $nomor++;
            
                    }

                    foreach(range('A','N') as $columnID) {
                        $spreadsheet->getActiveSheet()->getColumnDimension($columnID)
                            ->setAutoSize(true);
                    }

                    $writer = new Xlsx($spreadsheet);
        
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$tes['nama_tes'].' '.$file_data.'.xlsx"');
                    header('Cache-Control: max-age=0');
        
                    $writer->save('php://output');
                } else {
                    $semua_peserta = $this->tes->get_all("peserta", ["id_tes" => $tes['id_tes']]);
                    $spreadsheet->setActiveSheetIndex(0)
                                ->setCellValue('A1', '<h1>LIST PESERTA ' . $tes['nama_tes'] . '</h1>')
                                ->setCellValue('A2', 'No')
                                ->setCellValue('B2', 'Nama Lengkap')
                                ->setCellValue('C2', 'Email')
                                ->setCellValue('D2', 'Benar')
                                ->setCellValue('E2', 'Nilai');

                    $spreadsheet->getActiveSheet()->mergeCells('A1:N1');
                    
                    $kolom = 3;
                    $nomor = 1;
                    foreach($semua_peserta as $peserta) {
            
                            $spreadsheet->setActiveSheetIndex(0)
                                        ->setCellValue('A' . $kolom, $nomor)
                                        ->setCellValue('B' . $kolom, $peserta['nama'])
                                        ->setCellValue('C' . $kolom, $peserta['email'])
                                        ->setCellValue('D' . $kolom, $peserta['nilai'])
                                        ->setCellValue('E' . $kolom, skor_latihan($tes['id_tes'], $peserta['nilai']));
            
                            $kolom++;
                            $nomor++;
            
                    }
                    $writer = new Xlsx($spreadsheet);
        
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$tes['nama_tes'].'.xlsx"');
                    header('Cache-Control: max-age=0');
        
                    $writer->save('php://output');
                }
            }
        }
    // excel
    
    public function loadTes(){
        header('Content-Type: application/json');
        $output = $this->tes->loadTes();
        echo $output;
    }

    public function add_tes(){
        $data = $this->tes->add_tes();
        echo json_encode($data);
    }
    
    public function get_tes(){
        $id_tes = $this->input->post("id_tes");

        $data = $this->tes->get_one("tes", ["id_tes" => $id_tes]);
        echo json_encode($data);
    }

    public function loadHasil($tipe, $id){
        header('Content-Type: application/json');
        $output = $this->tes->loadHasil($tipe, $id);
        echo $output;
    }
    // load 
    
    public function get_peserta_toefl(){
        $data = $this->tes->get_peserta_toefl();
        echo json_encode($data);
    }

    public function edit_tes(){
        $data = $this->tes->edit_tes();
        echo json_encode($data);
    }

    public function change_status(){
        $data = $this->tes->change_status();
        echo json_encode($data);
    }

    public function edit_peserta_toefl(){
        $data = $this->tes->edit_peserta_toefl();
        echo json_encode($data);
    }

    public function hapus_tes(){
        $data = $this->tes->hapus_tes();
        echo json_encode($data);
    }

    public function upload_logo(){
        $data = $this->tes->upload_logo();
        echo json_encode($data);
    }

    public function add_sertifikat_toefl(){
        $data = $this->tes->add_sertifikat_toefl();
        echo json_encode($data);
    }
    
    public function sertifikat($id){
        $peserta = $this->tes->get_one("peserta_toefl", ["md5(id)" => $id]);
        $tes = $this->tes->get_one("tes", ["id_tes" => $peserta['id_tes']]);
        $peserta['nama'] = $peserta['nama'];
        $peserta['t4_lahir'] = ucwords(strtolower($peserta['t4_lahir']));
        $peserta['tahun'] = date('Y', strtotime($tes['tgl_tes']));
        $peserta['bulan'] = getRomawi(date('m', strtotime($tes['tgl_tes'])));
        $peserta['listening'] = poin("Listening", $peserta['nilai_listening']);
        $peserta['structure'] = poin("Structure", $peserta['nilai_structure']);
        $peserta['reading'] = poin("Reading", $peserta['nilai_reading']);
        $peserta['tgl_tes'] = $tes['tgl_tes'];

        $skor = ((poin("Listening", $peserta['nilai_listening']) + poin("Structure", $peserta['nilai_structure']) + poin("Reading", $peserta['nilai_reading'])) * 10) / 3;
        $peserta['skor'] = $skor;

        $skor = round($skor);
        
        $peserta['no_doc'] = "{$peserta['no_doc']}/AIMTP/{$peserta['bulan']}/{$peserta['tahun']}";

        $peserta['config'] = $this->tes->config();
        $peserta['id_tes'] = $peserta['id_tes'];
        
        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];
        
        $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => [148, 210], 'orientation' => 'L',
        // , 'margin_top' => '43', 'margin_left' => '25', 'margin_right' => '25', 'margin_bottom' => '35',
            'fontdata' => $fontData + [
                'rockb' => [
                    'R' => 'ROCKB.TTF',
                ],'rock' => [
                    'R' => 'ROCK.TTF',
                ],
                'arial' => [
                    'R' => 'arial.ttf',
                    'useOTL' => 0xFF,
                    'useKashida' => 75,
                ],
                'bodoni' => [
                    'R' => 'BOD_R.TTF',
                ],
                'calibri' => [
                    'R' => 'CALIBRI.TTF',
                ],
                'cambria' => [
                    'R' => 'CAMBRIAB.TTF',
                ]
            ], 
        ]);

        if($tes['tipe_tes'] == 'Tes TOEFL Umum'){
            $mpdf->SetTitle("{$peserta['nama']}");
            $mpdf->WriteHTML($this->load->view('pages/tes/sertifikat', $peserta, TRUE));
            $mpdf->Output("{$peserta['nama']}.pdf", "I");
        } else if($tes['tipe_tes'] == 'Tes TOEFL Kolaborasi'){
            $mpdf->SetTitle("{$peserta['nama']}");
            $mpdf->WriteHTML($this->load->view('pages/tes/sertifikat-kolaborasi', $peserta, TRUE));
            $mpdf->Output("{$peserta['nama']}.pdf", "I");
        } else if($tes['tipe_tes'] == 'Tes TOEFL Kursusan'){
            $mpdf->SetTitle("{$peserta['nama']}");
            $mpdf->WriteHTML($this->load->view('pages/tes/sertifikat-kursusan', $peserta, TRUE));
            $mpdf->Output("{$peserta['nama']}.pdf", "I");
        }

    }
    
    public function nilai(){
        $this->tes->add_data("nilai_toefl", ["soal" => 0, "poin" => 24, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 1, "poin" => 25, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 2, "poin" => 26, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 3, "poin" => 27, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 4, "poin" => 28, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 5, "poin" => 29, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 6, "poin" => 30, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 7, "poin" => 31, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 8, "poin" => 32, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 9, "poin" => 32, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 10, "poin" => 33, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 11, "poin" => 35, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 12, "poin" => 37, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 13, "poin" => 37, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 14, "poin" => 38, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 15, "poin" => 41, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 16, "poin" => 41, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 17, "poin" => 42, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 18, "poin" => 43, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 19, "poin" => 44, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 20, "poin" => 45, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 21, "poin" => 45, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 22, "poin" => 46, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 23, "poin" => 47, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 24, "poin" => 47, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 25, "poin" => 48, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 26, "poin" => 48, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 27, "poin" => 49, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 28, "poin" => 49, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 29, "poin" => 50, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 30, "poin" => 51, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 31, "poin" => 51, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 32, "poin" => 52, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 33, "poin" => 52, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 34, "poin" => 53, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 35, "poin" => 54, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 36, "poin" => 54, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 37, "poin" => 55, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 38, "poin" => 56, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 39, "poin" => 57, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 40, "poin" => 57, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 41, "poin" => 58, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 42, "poin" => 59, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 43, "poin" => 60, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 44, "poin" => 61, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 45, "poin" => 62, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 46, "poin" => 63, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 47, "poin" => 65, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 48, "poin" => 66, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 49, "poin" => 67, "tipe" => "Listening"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 50, "poin" => 68, "tipe" => "Listening"]);

        
        $this->tes->add_data("nilai_toefl", ["soal" => 0, "poin" => 20, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 1, "poin" => 20, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 2, "poin" => 21, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 3, "poin" => 22, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 4, "poin" => 23, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 5, "poin" => 25, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 6, "poin" => 26, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 7, "poin" => 27, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 8, "poin" => 29, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 9, "poin" => 31, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 10, "poin" => 33, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 11, "poin" => 35, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 12, "poin" => 36, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 13, "poin" => 37, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 14, "poin" => 38, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 15, "poin" => 40, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 16, "poin" => 40, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 17, "poin" => 41, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 18, "poin" => 42, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 19, "poin" => 43, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 20, "poin" => 44, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 21, "poin" => 45, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 22, "poin" => 46, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 23, "poin" => 47, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 24, "poin" => 48, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 25, "poin" => 49, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 26, "poin" => 50, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 27, "poin" => 51, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 28, "poin" => 52, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 29, "poin" => 53, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 30, "poin" => 54, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 31, "poin" => 55, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 32, "poin" => 56, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 33, "poin" => 57, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 34, "poin" => 58, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 35, "poin" => 60, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 36, "poin" => 61, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 37, "poin" => 63, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 38, "poin" => 65, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 39, "poin" => 67, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 40, "poin" => 68, "tipe" => "Structure"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 0, "poin" => 24, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 1, "poin" => 25, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 2, "poin" => 26, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 3, "poin" => 27, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 4, "poin" => 28, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 5, "poin" => 29, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 6, "poin" => 30, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 7, "poin" => 31, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 8, "poin" => 32, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 9, "poin" => 32, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 10, "poin" => 33, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 11, "poin" => 35, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 12, "poin" => 37, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 13, "poin" => 37, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 14, "poin" => 38, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 15, "poin" => 41, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 16, "poin" => 41, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 17, "poin" => 42, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 18, "poin" => 43, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 19, "poin" => 44, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 20, "poin" => 45, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 21, "poin" => 45, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 22, "poin" => 46, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 23, "poin" => 47, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 24, "poin" => 47, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 25, "poin" => 48, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 26, "poin" => 48, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 27, "poin" => 49, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 28, "poin" => 49, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 29, "poin" => 50, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 30, "poin" => 51, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 31, "poin" => 51, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 32, "poin" => 52, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 33, "poin" => 52, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 34, "poin" => 53, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 35, "poin" => 54, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 36, "poin" => 54, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 37, "poin" => 55, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 38, "poin" => 56, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 39, "poin" => 57, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 40, "poin" => 57, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 41, "poin" => 58, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 42, "poin" => 59, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 43, "poin" => 60, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 44, "poin" => 61, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 45, "poin" => 62, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 46, "poin" => 63, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 47, "poin" => 65, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 48, "poin" => 66, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 49, "poin" => 67, "tipe" => "Reading"]);
        $this->tes->add_data("nilai_toefl", ["soal" => 50, "poin" => 68, "tipe" => "Reading"]);
    }
}

/* End of file Tes.php */
