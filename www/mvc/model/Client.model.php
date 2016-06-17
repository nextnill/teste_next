<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class Client_Model extends \Sys\Model {
    
    public $id;
    public $excluido;

    public $name;
    public $code;
    public $doc_exig_com_inv;
    public $doc_exig_pack_list;
    public $doc_exig_bl;
    public $doc_exig_certif_orig;
    public $doc_exig_proforma_invoice;
    public $doc_exig_fumigation_certificate;
    public $doc_exig_bill_of_lading;
    public $head_office_id;
    public $terms_of_payment;
    public $contact;
    public $telephone;
    public $mobile;
    public $fax;
    public $email;
    public $contact_other;
    public $eori;
    public $client_groups;
    public $agencies;
    public $consignee;
    public $notify_address;
    public $marks;
    public $destination_port;
    public $port_of_loading;
    public $obs_body_of_bl;
    public $desc_of_goods;
    public $obs;

    public $branch_offices;

    
    function __construct()
    {
        parent::__construct();
    }

    function validation()
    {
        $validation = new Validation();
        
        if (strlen($this->name) == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the name');
        }
        
        return $validation;
    }
    
    function save()
    {
        if (!$this->exists())
        {
            return $this->insert();
        }
        else
        {
            return $this->update();
        }
    }
    
    function exists()
    {
        if (is_null($this->id))
        {
            $this->id = 0;
        }
        $query = DB::query('SELECT id FROM client WHERE id = ? ', array($this->id));
        
        if (DB::has_rows($query))
        {
            return true;
        }
        return false;
    }
    
    function insert()
    {
        $validation = $this->validation();

        if ($validation->isValid())
        {
            $sql = 'INSERT INTO client (
                        name,
                        code,
                        doc_exig_com_inv,
                        doc_exig_pack_list,
                        doc_exig_bl,
                        doc_exig_certif_orig,
                        doc_exig_proforma_invoice,
                        doc_exig_fumigation_certificate,
                        doc_exig_bill_of_lading,
                        head_office_id,
                        terms_of_payment,
                        contact,
                        telephone,
                        mobile,
                        fax,
                        email,
                        contact_other,
                        eori,
                        consignee,
                        notify_address,
                        marks,
                        destination_port,
                        obs_body_of_bl,
                        desc_of_goods,
                        obs
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    ) ';

            $query = DB::exec($sql, array(
                // values
                $this->name,
                $this->code,
                $this->doc_exig_com_inv,
                $this->doc_exig_pack_list,
                $this->doc_exig_bl,
                $this->doc_exig_certif_orig,
                $this->doc_exig_proforma_invoice,
                $this->doc_exig_fumigation_certificate,
                $this->doc_exig_bill_of_lading,
                ($this->head_office_id == 0 ? null : $this->head_office_id),
                $this->terms_of_payment,
                $this->contact,
                $this->telephone,
                $this->mobile,
                $this->fax,
                $this->email,
                $this->contact_other,
                $this->eori,
                $this->consignee,
                $this->notify_address,
                $this->marks,
                $this->destination_port,
                $this->obs_body_of_bl,
                $this->desc_of_goods,
                $this->obs
            ));

            $this->id = DB::last_insert_id();
            
            $this->save_client_groups();
            $this->save_agencies();
            $this->save_port_of_loading();
            //$this->save_port_of_discharge();

            return $this->id;
        }
        
        return $validation;
    }
    
    function update()
    {
        $validation = $this->validation();
        
        if ($validation->isValid())
        {
            if (!$this->exists())
            {
                $validation = new Validation();
                $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Id does not exists');
            }
            else
            {
                $sql = '
                    UPDATE
                        client
                    SET
                        name = ?,
                        code = ?,
                        doc_exig_com_inv = ?,
                        doc_exig_pack_list = ?,
                        doc_exig_bl = ?,
                        doc_exig_certif_orig = ?,
                        doc_exig_proforma_invoice = ?,
                        doc_exig_fumigation_certificate = ?,
                        doc_exig_bill_of_lading = ?,
                        head_office_id = ?,
                        terms_of_payment = ?,
                        contact = ?,
                        telephone = ?,
                        mobile = ?,
                        fax = ?,
                        email = ?,
                        contact_other = ?,
                        eori = ?,
                        consignee = ?,
                        notify_address = ?,
                        marks = ?,
                        destination_port = ?,
                        obs_body_of_bl = ?,
                        desc_of_goods = ?,
                        obs = ?
                    WHERE
                        id = ? ';
                $query = DB::exec($sql, array(
                    // set
                    $this->name,
                    $this->code,
                    $this->doc_exig_com_inv,
                    $this->doc_exig_pack_list,
                    $this->doc_exig_bl,
                    $this->doc_exig_certif_orig,
                    $this->doc_exig_proforma_invoice,
                    $this->doc_exig_fumigation_certificate,
                    $this->doc_exig_bill_of_lading,
                    ($this->head_office_id == 0 ? null : $this->head_office_id),
                    $this->terms_of_payment,
                    $this->contact,
                    $this->telephone,
                    $this->mobile,
                    $this->fax,
                    $this->email,
                    $this->contact_other,
                    $this->eori,
                    $this->consignee,
                    $this->notify_address,
                    $this->marks,
                    $this->destination_port,
                    $this->obs_body_of_bl,
                    $this->desc_of_goods,
                    $this->obs,
                    // where
                    $this->id
                ));
                
                $this->save_client_groups();
                $this->save_agencies();
                $this->save_port_of_loading();
                //$this->save_port_of_discharge();

                return $this->id;
            }
        }
        
        return $valid;
    }

    // client_groups
    private function save_client_groups()
    {
        $this->delete_client_groups();
        $this->insert_client_groups();
    }

    private function delete_client_groups()
    {
        $sql = 'DELETE FROM client_group_client WHERE client_id = ?';
        $params[0] = $this->id;
        $query = DB::exec($sql, $params);
    }

    private function insert_client_groups()
    {
        if (is_array($this->client_groups)) {
            foreach ($this->client_groups as $key => $value) {
                $sql = 'INSERT INTO client_group_client (client_group_id, client_id) VALUES (?, ?)';
                $params[0] = $value;
                $params[1] = $this->id;
                $query = DB::exec($sql, $params);
            }
        }
    }

    // agencies
    private function save_agencies()
    {
        $this->delete_agencies();
        $this->insert_agencies();
    }

    private function delete_agencies()
    {
        $sql = 'DELETE FROM client_agency WHERE client_id = ?';
        $params[0] = $this->id;
        $query = DB::exec($sql, $params);
    }

    private function insert_agencies()
    {
        if (is_array($this->agencies)) {
            foreach ($this->agencies as $key => $value) {
                $sql = 'INSERT INTO client_agency (agency_id, client_id) VALUES (?, ?)';
                $params[0] = $value;
                $params[1] = $this->id;
                $query = DB::exec($sql, $params);
            }
        }
    }

    // port of loading
    private function save_port_of_loading()
    {
        $this->delete_port_of_loading();
        $this->insert_port_of_loading();
    }

    private function delete_port_of_loading()
    {
        $sql = 'DELETE FROM client_port_loading WHERE client_id = ?';
        $params[0] = $this->id;
        $query = DB::exec($sql, $params);
    }

    private function insert_port_of_loading()
    {
        if (is_array($this->port_of_loading)) {
            foreach ($this->port_of_loading as $key => $value) {
                $sql = 'INSERT INTO client_port_loading (terminal_id, client_id) VALUES (?, ?)';
                $params[0] = $value;
                $params[1] = $this->id;
                $query = DB::exec($sql, $params);
            }
        }
    }

    // port of discharge
    /*
    private function save_port_of_discharge()
    {
        $this->delete_port_of_discharge();
        $this->insert_port_of_discharge();
    }

    private function delete_port_of_discharge()
    {
        $sql = 'DELETE FROM client_port_discharge WHERE client_id = ?';
        $params[0] = $this->id;
        $query = DB::exec($sql, $params);
    }

    private function insert_port_of_discharge()
    {
        if (is_array($this->port_of_discharge)) {
            foreach ($this->port_of_discharge as $key => $value) {
                $sql = 'INSERT INTO client_port_discharge (terminal_id, client_id) VALUES (?, ?)';
                $params[0] = $value;
                $params[1] = $this->id;
                $query = DB::exec($sql, $params);
            }
        }
    }
    */

    function delete()
    {
        $validation = new Validation();
        
        if (!$this->exists())
        {
            $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Id does not exists');
        }
        else
        {
            $sql = 'UPDATE client SET excluido = ? WHERE id = ? ';
            $query = DB::exec($sql, array('S', $this->id));
            
            return $this->id;
        }
        
        return $validation;
    }
    
    function populate($id)
    {
        $validation = new Validation();
        
        if ($id) {
            $this->id = $id;
        }
        
        if (!$this->exists())
        {
            $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Id does not exists');
        }
        else
        {
            $query = DB::query(
                'SELECT
                    id,
                    excluido,
                    name,
                    code,
                    doc_exig_com_inv,
                    doc_exig_pack_list,
                    doc_exig_bl,
                    doc_exig_certif_orig,
                    doc_exig_proforma_invoice,
                    doc_exig_fumigation_certificate,
                    doc_exig_bill_of_lading,
                    head_office_id,
                    terms_of_payment,
                    contact,
                    telephone,
                    mobile,
                    fax,
                    email,
                    contact_other,
                    eori,
                    consignee,
                    notify_address,
                    marks,
                    destination_port,
                    obs_body_of_bl,
                    desc_of_goods,
                    obs
                FROM
                    client
                WHERE id = ?',
                array($id)
            );
            
            if (DB::has_rows($query))
            {
                $this->fill($query[0]);

                //populate client_groups
                $sql = 'SELECT client_group_id FROM client_group_client WHERE client_id = ?';
                $params[0] = $this->id;
                $query_client_group = DB::query($sql, $params);
                $this->client_groups = array();
                foreach ($query_client_group as $row) {
                    $this->client_groups[] = (int)$row['client_group_id'];
                }

                //populate agencies
                $sql = 'SELECT agency_id FROM client_agency WHERE client_id = ?';
                $params[0] = $this->id;
                $query_agencies = DB::query($sql, $params);
                $this->agencies = array();
                foreach ($query_agencies as $row) {
                    $this->agencies[] = (int)$row['agency_id'];
                }

                //populate port of loading
                $sql = 'SELECT terminal_id FROM client_port_loading WHERE client_id = ?';
                $params[0] = $this->id;
                $query_ports = DB::query($sql, $params);
                $this->port_of_loading = array();
                foreach ($query_ports as $row) {
                    $this->port_of_loading[] = (int)$row['terminal_id'];
                }

                //populate port of discharge
                /*
                $sql = 'SELECT terminal_id FROM client_port_discharge WHERE client_id = ?';
                $params[0] = $this->id;
                $query_ports = DB::query($sql, $params);
                $this->port_of_discharge = array();
                foreach ($query_ports as $row) {
                    $this->port_of_discharge[] = (int)$row['terminal_id'];
                }
                */

                return $this->id;
            }
        }

        return $validation;
    }

    function fill($row_query)
    {
        if ($row_query)
        {
            $this->id = (int)$row_query['id'];
            $this->excluido = (string)$row_query['excluido'];

            $this->name = (string)$row_query['name'];
            $this->code = (string)$row_query['code'];
            $this->doc_exig_com_inv = (string)$row_query['doc_exig_com_inv'];
            $this->doc_exig_pack_list = (string)$row_query['doc_exig_pack_list'];
            $this->doc_exig_bl = (string)$row_query['doc_exig_bl'];
            $this->doc_exig_certif_orig = (string)$row_query['doc_exig_certif_orig'];
            $this->doc_exig_proforma_invoice = (string)$row_query['doc_exig_proforma_invoice'];
            $this->doc_exig_fumigation_certificate = (string)$row_query['doc_exig_fumigation_certificate'];
            $this->doc_exig_bill_of_lading = (string)$row_query['doc_exig_bill_of_lading'];
            $this->head_office_id = (!empty($row_query['head_office_id']) ? (int)$row_query['head_office_id'] : null);
            $this->terms_of_payment = (string)$row_query['terms_of_payment'];
            $this->contact = (string)$row_query['contact'];
            $this->telephone = (string)$row_query['telephone'];
            $this->mobile = (string)$row_query['mobile'];
            $this->fax = (string)$row_query['fax'];
            $this->email = (string)$row_query['email'];
            $this->contact_other = (string)$row_query['contact_other'];
            $this->eori = (string)$row_query['eori'];
            $this->consignee = (string)$row_query['consignee'];
            $this->notify_address = (string)$row_query['notify_address'];
            $this->marks = (string)$row_query['marks'];
            $this->destination_port = (string)$row_query['destination_port'];
            //$this->port_of_discharge = (string)$row_query['port_of_discharge'];
            //$this->port_of_loading = (string)$row_query['port_of_loading'];
            $this->obs_body_of_bl = (string)$row_query['obs_body_of_bl'];
            $this->desc_of_goods = (string)$row_query['desc_of_goods'];
            $this->obs = (string)$row_query['obs'];

            $this->branch_offices = $this->get_branch_offices();
        }
    }
    
    function get_list($excluido=false)
    {
        $sql = 'SELECT
                    client.id,
                    client.name,
                    client.code,
                    client.head_office_id,
                    head_office.name AS head_office_name,
                    client.excluido
                FROM client
                LEFT JOIN client AS head_office ON (head_office.id = client.head_office_id)
                WHERE client.excluido = ?
                ORDER BY concat(coalesce(head_office.name, \'\'), client.name) ';

        $params[] = ($excluido === true ? 'S' : 'N');

        $query = DB::query($sql, $params);

        return $query;
    }

    function get_list_by_client_group($client_group_id)
    {
        $sql = 'SELECT
                    client.id,
                    client.name,
                    client.code
                FROM client
                INNER JOIN client_group_client ON (client_group_client.client_id = client.id AND client_group_id = ?)
                WHERE client.excluido = ? 
                ORDER BY client.code ';

        $params[] = (int)$client_group_id;
        $params[] = 'N';

        $query = DB::query($sql, $params);

        return $query;
    }

    function get_list_head_office()
    {
        $query = DB::query('SELECT id, name, code FROM client WHERE excluido = ? AND head_office_id IS NULL ORDER BY name', array('N'));
        return $query;
    }

    function get_branch_offices()
    {
        $query = DB::query('SELECT id, name, code FROM client WHERE excluido = ? AND head_office_id = ? ORDER BY name', array('N', $this->id));
        return $query;
    }
    
}