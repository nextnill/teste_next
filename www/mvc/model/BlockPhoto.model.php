<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class BlockPhoto_Model extends \Sys\Model {

    public $id;
    public $excluido;
    public $date_record;

    public $production_order_item_id;
    public $block_id;
    public $path;
    public $file;
    public $type;
    public $size;
    public $obs;

    public $small_url;
    public $large_url;

    function __construct()
    {
        parent::__construct();
    }

    function validation()
    {
        $validation = new Validation();
        
        if (!$this->production_order_item_id > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the lot transport');
        }
        
        return $validation;
    }
    
    function save($file)
    {
        if (!$this->exists())
        {
            //if (!is_null($file)) {
                $this->path = BLOCK_PHOTO_PATH;
                $this->file = $file['name'];;
                $this->type = $file['type'];
                $this->size = $file['size'];
                
                $this->insert();

                if ($this->id > 0) {
                    $destination_path = BLOCK_PHOTO_PATH . '/photo_' . $this->id;

                    // mover arquivo temporario para o destino
                    move_uploaded_file($file['tmp_name'], $destination_path);

                    // gero uma imagem grande, porém com menos qualidade que a original
                    $original_img = null;
                    $imagetype = exif_imagetype($destination_path);

                    switch ($imagetype) {
                        case IMAGETYPE_GIF:
                            $original_img = @imagecreatefromgif($destination_path);
                            break;
                        case IMAGETYPE_JPEG:
                            $original_img = @imagecreatefromjpeg($destination_path);
                            break;
                        case IMAGETYPE_PNG:
                            $original_img = @imagecreatefrompng($destination_path);
                            break;
                    }

                    if (!is_null($original_img)) {
                        $original_x = ImagesX($original_img);
                        $original_y = ImagesY($original_img);

                        $new_y = 0;
                        $new_x = 0;

                        // paisagem
                        if ($original_x >= $original_y) {
                            $new_x = 640;
                            $new_y = intval(($original_y * $new_x) / $original_x);
                        }
                        // retrato
                        else {
                            $new_y = 640; //max y
                            $new_x = intval(($original_x * $new_y) / $original_y);
                        }

                        $photo_final = ImageCreateTrueColor($new_x, $new_y);
                        ImageCopyResampled($photo_final, $original_img, 0, 0, 0, 0, $new_x+1, $new_y+1, $original_x, $original_y); 
                        ImageJPEG($photo_final, BLOCK_PHOTO_LARGE_PATH . '/photo_' . $this->id . '.jpg', 75);
                        ImageDestroy($photo_final);
                        
                        @unlink($destination_path);
                    }

                }

                return $this;
            //}
        }
        else
        {
            return $this->update();
        }
    }

    function request_img($size)
    {
        $file_show = null;

        switch ($size) {
            case 'small':
                $file_show = BLOCK_PHOTO_SMALL_PATH . '/photo_' . $this->id . '.jpg';
                break;
            case 'large':
                $file_show = BLOCK_PHOTO_LARGE_PATH . '/photo_' . $this->id . '.jpg';
                break;
        }

        // se não existir o arquivo solicitado
        if (!file_exists($file_show)) {
            // conversão..

            // se for imagem pequena, pega da foto larga
            if ($size == 'small') {
                $large_path = BLOCK_PHOTO_LARGE_PATH . '/photo_' . $this->id . '.jpg';
                if (file_exists($large_path)) {
                    $large_img = @imagecreatefromjpeg($large_path);
                    
                    if (!is_null($large_img)) {
                        $large_x = ImagesX($large_img);
                        $large_y = ImagesY($large_img);

                        
                        $new_y = 120; //max y
                        $new_x = intval(($large_x * $new_y) / $large_y);

                        $photo_final = ImageCreateTrueColor($new_x, $new_y);
                        ImageCopyResampled($photo_final, $large_img, 0, 0, 0, 0, $new_x+1, $new_y+1, $large_x, $large_y); 
                        ImageJPEG($photo_final, BLOCK_PHOTO_SMALL_PATH . '/photo_' . $this->id . '.jpg', 75);
                        ImageDestroy($photo_final);
                    }
                }
            }
        }

        return $file_show;
    }
    
    function exists()
    {
        if (is_null($this->id))
        {
            $this->id = 0;
        }
        $query = DB::query('SELECT id FROM block_photo WHERE id = ? ', array($this->id));
        
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
            $sql = 'INSERT INTO block_photo (
                        date_record,
                        production_order_item_id,
                        block_id,
                        path,
                        file,
                        type,
                        size,
                        obs
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?
                    ) ';
            
            $dt_now = new DateTime('now');
            $dt_now = $dt_now->format('Y-m-d H:i:s');
            $params[] = $dt_now;
            $params[] = (int)$this->production_order_item_id;
            $params[] = ((int)$this->block_id > 0 ? (int)$this->block_id : null);
            $params[] = BLOCK_PHOTO_PATH;
            $params[] = (string)$this->file;
            $params[] = (string)$this->type;
            $params[] = (int)$this->size;
            $params[] = (string)$this->obs;
            
            $query = DB::exec($sql, $params);

            $this->id = DB::last_insert_id();
            $this->populate($this->id);

            return $this;
        }
        
        return array('validation' => $validation);
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
                $sql = 'UPDATE
                            block_photo
                        SET
                            production_order_item_id = ?,
                            block_id = ?,
                            path = ?,
                            file = ?,
                            type = ?,
                            size = ?,
                            obs = ?
                        WHERE
                            id = ? ';
                // set
                $params[] = (int)$this->production_order_item_id;
                $params[] = ((int)$this->block_id > 0 ? (int)$this->block_id : null);
                $params[] = (string)$this->path;
                $params[] = (string)$this->file;
                $params[] = (string)$this->type;
                $params[] = (int)$this->size;
                $params[] = (string)$this->obs;

                // where
                $params[] = (int)$this->id;

                $query = DB::exec($sql, $params);

                return $this;
            }
        }
        
        return array('validation' => $validation);
    }

    function delete()
    {
        $validation = new Validation();
        
        if (!$this->exists())
        {
            $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Id does not exists');
        }
        else
        {
            $sql = 'UPDATE block_photo SET excluido = ? WHERE id = ? ';
            
            $params[] = 'S';
            $params[] = $this->id;

            $query = DB::exec($sql, $params);
            
            $this->excluido = 'S';
            return $this;
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
                    date_record,
                    production_order_item_id,
                    block_id,
                    path,
                    file,
                    type,
                    size,
                    obs
                FROM
                    block_photo
                WHERE id = ?',
                array($id)
            );
            
            if (DB::has_rows($query))
            {
                $this->fill($query[0]);
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
            $this->date_record = (string)$row_query['date_record'];

            $this->production_order_item_id = (int)$row_query['production_order_item_id'];
            $this->block_id = (empty($row_query['block_id']) ? null : (int)$row_query['block_id']);
            $this->path = (string)$row_query['path'];
            $this->file = (string)$row_query['file'];
            $this->type = (string)$row_query['type'];
            $this->size = (int)$row_query['size'];
            $this->obs = (string)$row_query['obs'];
            
            $this->small_url = APP_URI . 'block/photo/' . $row_query['id'] . '/small/view.jpg';
            $this->large_url = APP_URI . 'block/photo/' . $row_query['id'] . '/large/view.jpg';
        }
        return $this;
    }

    // listar fotos de um bloco de uma op
    function get_by_poi($production_order_item_id)
    {
        $sql = 'SELECT
                    id,
                    excluido,
                    date_record,
                    production_order_item_id,
                    block_id,
                    path,
                    file,
                    type,
                    size,
                    obs
                FROM block_photo
                WHERE production_order_item_id = ? 
                    AND excluido = \'N\' ';

        $params[] = $production_order_item_id;

        $query = DB::query($sql, $params);
        
        $photos = array();

        foreach ($query as $key => $row) {
            $photo = new self;
            $photo->fill($row);
            $photos[] = $photo;
        }

        return $photos;
    }

    // listar fotos de um bloco já produzido
    function get_by_block($production_order_item_id)
    {
        $sql = 'SELECT
                    id,
                    excluido,
                    date_record,
                    production_order_item_id,
                    block_id,
                    path,
                    file,
                    type,
                    size,
                    obs
                FROM block_photo
                WHERE production_order_item_id = ?
                    AND excluido = \'N\' ';

        $params[] = $production_order_item_id;


        $query = DB::query($sql, $params);

        $photos = array();

        foreach ($query as $key => $row) {
            $photo = new self;
            $photo->fill($row);
            $photos[] = $photo;
        }
        
        return $photos;
    }

}