<?php namespace CCTM\Model;

use CCTM\Exceptions\FileNotFoundException;
use CCTM\Exceptions\NotFoundException;

class BaseModel {

    protected $dic;
    protected $attr;
    protected $id;
    protected $subdir; // starts with leading slash
    protected $ext = '.json';
    protected $pk; // primary key (should be one of the attributes)
    protected $context = 'update'; // create | update

    public function __construct($dic, array $attributes = array())
    {
        $this->dic = $dic;
        $this->attr = $attributes;
        // For testing the BaseModel, otherwise $this->subdir set in the child class
        if (empty($this->subdir))
        {
            $this->subdir = $dic['subdir'];
        }
        if (empty($this->pk)) {
            $this->pk = $dic['pk'];
        }
        if (!empty($attributes))
        {
            $this->context = 'create';
        }
    }

    /**
     * Gets the rel path to the storage directory with trailing slash
     * @return string
     */
    public function getLocalDir()
    {
        return trim($this->subdir,'/') . '/';
    }

    /**
     * Relative filename
     *
     * @param $id
     *
     * @return string
     * @throws NotFoundException
     */
    public function getFilename($id)
    {
        // Avoid directory transversing
        if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $id))
        {
            throw new NotFoundException('Invalid resource name');
        }
        return $this->getLocalDir().$id.$this->ext;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Says whether we are updating an existing item or creating a new one
     */
    public function isNew()
    {
        return ($this->context == 'create');
    }

    public function getItem($id)
    {
        //$this->dic['Filesystem']->read($id);
        // TODO: permissions: can read?


        if (!$exists = $this->dic['Filesystem']->has($this->getFilename($id)))
        {
            throw new FileNotFoundException('File not found: '.$this->getLocalDir().$id.'.json');
        }

        $this->attr = $this->dic['JsonDecoder']->decode($this->dic['Filesystem']->read($this->getFilename($id)));
        $this->context = 'update';
        $this->id = $id;
        return $this->attr;
    }

    public function getCollection(array $filters=array())
    {
        // TODO: cache this so we're not having to iterate and parse every @!% file
    }

    public function get($key)
    {
        return (isset($this->attr[$key])) ? $this->attr[$key] : null;
    }

    public function set($key, $value)
    {
        $this->attr[$key] = $value;
    }


    public function save()
    {
        // Check PK
        // Validate

        $this->context = 'update';


        //;
        //$this->dic['Filesystem']->put($file, $this->dic['JsonEncoder']->encode($this->attr));
    }
}

/*EOF*/