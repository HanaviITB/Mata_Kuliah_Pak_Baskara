<?php

require 'config/Database.php';
use Database\Database;

class Pasien {

    private $db;
    private $table_name = "tbpasien";

    public function __construct(){
        $this->db = new Database();
        $this->db->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function execute($query){
        $stmt = $this->db->query($query);
        if($stmt){
          $result = array(
            "code"      => 200,
            "message"   => "OK",
            "data"      => $stmt->fetchAll(PDO::FETCH_ASSOC)
          );
        }else{
          $result = array(
            "code" => 400,
            "message" => $this->db->lasterror,
            "data" => array()
          );            
        }
        return $result;
    }

    public function all($page){
      $perpage = 30;
      $start = $perpage * (intval($page) - 1)  ;

      $fields = "*";
      $query = $this->db->query( "SELECT * FROM " . $this->table_name . " LIMIT " . $start . ", " . $perpage);
      if($query){
        $result = array(
          "code"      => 200,
          "message"   => "OK",
          "data"      => $query->fetchAll(PDO::FETCH_ASSOC)
        );
      }else{
          if(count($query) < 1){
            $result = array(
              "code"      => 404,
              "message"   => "Data tidak ditemukan",
              "data"      => $query
            );                
          }else{
            $result = array(
              "code" => 400,
              "message" => $this->db->lasterror,
              "data" => array()
            );
          }
      }
      return $result;
    }

    public function get($id){
        $fields = "*";
        $query = $this->db->select($this->table_name, $fields, array("No_MR" => $id) );
        if($query){
          $result = array(
            "code"      => 200,
            "message"   => "OK",
            "data"      => $query
          );
        }else{
            if(count($query) < 1){
              $result = array(
                "code"      => 404,
                "message"   => "Data tidak ditemukan",
                "data"      => $query
              );                
            }else{
              $result = array(
                "code" => 400,
                "message" => $this->db->lasterror,
                "data" => array()
              );
            }
        }
        return $result;
    }

    public function save($data){
        $query = $this->db->insert($this->table_name, $data);
        if($query){
          $result = array(
            "code"      => 200,
            "message"   => "OK",
            "data"      => array($data)
          );
        }else{
          $result = array(
            "code" => 400,
            "message" => $this->db->lasterror,
            "data" => array()
          );
        }
        return $result;
    }

    public function update($data, $id){
        $put = $this->db->update($this->table_name, $data, array('Noauto' => $id ));
        if($put){
          $result = array(
            "code"      => 200,
            "message"   => "OK",
            "data"      => array($data)
          );
        }else{
          $result = array(
            "code" => 400,
            "message" => $this->db->lasterror,
            "data" => array()
          );
        }
        return $result;
    }

    public function delete($id){
        $delete = $this->db->delete($this->table_name, array('Noauto' => $id ));
        if($delete){
          $result = array(
            "code"      => 200,
            "message"   => "OK",
            "data"      => array()
          );
        }else{
          $result = array(
            "code" => 400,
            "message" => $this->db->lasterror,
            "data" => array()
          );
        }
        return $result;
    }

    public function profile($nomr){
      $nomr = str_replace("_", "/", $nomr);
      $query = $this->db->query("SELECT No_MR, Nama_Pasien, Jk, Alamat_Pasien, Tgl_Lahir, (YEAR(CURDATE()) - YEAR(Tgl_Lahir)) AS Usia, HP
                                  FROM tbpasien WHERE No_MR = '$nomr'");
      if($query){
        $result = array(
          "code"      => 200,
          "message"   => "OK",
          "data"      => $query->fetchAll(PDO::FETCH_ASSOC)
        );
      }else{
        $result = array(
          "code" => 400,
          "message" => $this->db->lasterror,
          "data" => array()
        );
      }
      return $result;
    }

    public function history($nomr){
      $nomr = str_replace("_", "/", $nomr);
      $header = $this->db->query("SELECT No_MR, Nama_Pasien, (YEAR(CURDATE()) - YEAR(Tgl_Lahir)) AS Usia FROM tbpasien WHERE No_MR = '$nomr' ")->fetchAll(PDO::FETCH_ASSOC);
      $query = $this->db->query("SELECT c.Tanggal, (YEAR(c.Tanggal) - YEAR(b.Tgl_Lahir)) AS Usia, d.Diagnosa
                                  FROM tbpendaftaran a INNER JOIN tbpasien b ON a.No_MR = b.No_MR
                                  INNER JOIN tbtransaksipenjualan c ON a.No_Pendaftaran=c.No_Pendaftaran
                                  INNER JOIN tbdiagnosa d ON c.no_urut=d.no_urut
                                  WHERE a.No_MR = '$nomr' ORDER BY c.Tanggal DESC ")->fetchAll(PDO::FETCH_ASSOC);

      $header[0]['history'] = $query;
      if($query){
        $result = array(
          "code"      => 200,
          "message"   => "OK",
          "data"      => $header
        );
      }else{
        $result = array(
          "code" => 400,
          "message" => $this->db->lasterror,
          "data" => array()
        );
      }
      return $result;
    }

    public function topDeases(){
      $query = $this->db->query("SELECT h.no_urut,h.Diagnosa, h.Tanggal,
                                  (SELECT COUNT(y.No_MR) FROM tbpendaftaran y, tbtransaksipenjualan z WHERE y.no_pendaftaran = z.no_pendaftaran AND z.no_urut = h.no_urut ) AS JmlPx,
                                  (SELECT COUNT(y.No_MR) FROM tbpasien x, tbpendaftaran y, tbtransaksipenjualan z 
                                    WHERE x.No_MR = y.No_MR AND y.no_pendaftaran = z.no_pendaftaran AND z.no_urut = h.no_urut AND x.jk='Pria' ) AS JmlPxLaki,
                                  (SELECT COUNT(y.No_MR) FROM tbpasien x, tbpendaftaran y, tbtransaksipenjualan z 
                                    WHERE x.No_MR = y.No_MR AND y.no_pendaftaran = z.no_pendaftaran AND z.no_urut = h.no_urut AND x.jk = 'Wanita' ) AS JmlPxWanita 
                                  FROM (SELECT b.no_urut, b.diagnosa, COUNT(b.no_urut) AS jml, a.Tanggal
                                        FROM tbtransaksipenjualan AS a, tbdiagnosa AS b
                                        WHERE a.no_urut = b.no_urut GROUP BY  b.no_urut, b.diagnosa ORDER BY  COUNT(b.no_urut) DESC  LIMIT 10 
                                      ) h");

      if($query){
        $result = array(
          "code"      => 200,
          "message"   => "OK",
          "data"      => $query->fetchAll(PDO::FETCH_ASSOC)
        );
      }else{
        $result = array(
          "code" => 400,
          "message" => $this->db->lasterror,
          "data" => array()
        );
      }
      return $result;
    }

    public function topDeasesTahun($tahun){
      $query = $this->db->query("SELECT * FROM (
                                  SELECT h.no_urut,h.Diagnosa, h.Tanggal,
                                  (SELECT COUNT(y.No_MR) FROM tbpendaftaran y, tbtransaksipenjualan z WHERE y.no_pendaftaran = z.no_pendaftaran AND z.no_urut = h.no_urut ) AS JmlPx,
                                  (SELECT COUNT(y.No_MR) FROM tbpasien x, tbpendaftaran y, tbtransaksipenjualan z 
                                    WHERE x.No_MR = y.No_MR AND y.no_pendaftaran = z.no_pendaftaran AND z.no_urut = h.no_urut AND x.jk='Pria' ) AS JmlPxLaki,
                                  (SELECT COUNT(y.No_MR) FROM tbpasien x, tbpendaftaran y, tbtransaksipenjualan z 
                                    WHERE x.No_MR = y.No_MR AND y.no_pendaftaran = z.no_pendaftaran AND z.no_urut = h.no_urut AND x.jk = 'Wanita' ) AS JmlPxWanita 
                                  FROM (SELECT b.no_urut, b.diagnosa, COUNT(b.no_urut) AS jml, a.Tanggal
                                        FROM tbtransaksipenjualan AS a, tbdiagnosa AS b
                                        WHERE a.no_urut = b.no_urut GROUP BY  b.no_urut, b.diagnosa ORDER BY  COUNT(b.no_urut) DESC  LIMIT 10 
                                      ) h ) xx WHERE YEAR(Tanggal) = '$tahun' ");

      if($query){
        $result = array(
          "code"      => 200,
          "message"   => "OK",
          "data"      => $query->fetchAll(PDO::FETCH_ASSOC)
        );
      }else{
        $result = array(
          "code" => 400,
          "message" => $this->db->lasterror,
          "data" => array()
        );
      }
      return $result;
    }

    public function topDeasesBulanTahun($bulan, $tahun){
      $query = $this->db->query("SELECT * FROM (
                                  SELECT h.no_urut,h.Diagnosa, h.Tanggal,
                                  (SELECT COUNT(y.No_MR) FROM tbpendaftaran y, tbtransaksipenjualan z WHERE y.no_pendaftaran = z.no_pendaftaran AND z.no_urut = h.no_urut ) AS JmlPx,
                                  (SELECT COUNT(y.No_MR) FROM tbpasien x, tbpendaftaran y, tbtransaksipenjualan z 
                                    WHERE x.No_MR = y.No_MR AND y.no_pendaftaran = z.no_pendaftaran AND z.no_urut = h.no_urut AND x.jk='Pria' ) AS JmlPxLaki,
                                  (SELECT COUNT(y.No_MR) FROM tbpasien x, tbpendaftaran y, tbtransaksipenjualan z 
                                    WHERE x.No_MR = y.No_MR AND y.no_pendaftaran = z.no_pendaftaran AND z.no_urut = h.no_urut AND x.jk = 'Wanita' ) AS JmlPxWanita 
                                  FROM (SELECT b.no_urut, b.diagnosa, COUNT(b.no_urut) AS jml, a.Tanggal
                                        FROM tbtransaksipenjualan AS a, tbdiagnosa AS b
                                        WHERE a.no_urut = b.no_urut GROUP BY  b.no_urut, b.diagnosa ORDER BY  COUNT(b.no_urut) DESC  LIMIT 10 
                                      ) h ) xx WHERE MONTH(Tanggal) = '$bulan' AND YEAR(Tanggal) = '$tahun' ");

      if($query){
        $result = array(
          "code"      => 200,
          "message"   => "OK",
          "data"      => $query->fetchAll(PDO::FETCH_ASSOC)
        );
      }else{
        $result = array(
          "code" => 400,
          "message" => $this->db->lasterror,
          "data" => array()
        );
      }
      return $result;
    }

}