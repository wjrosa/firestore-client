<?php
namespace WJRosa\Firestore;

class Document
{
    /**
     * @var array|mixed
     */
    private $fields = [];

    /**
     * @var null|string
     */
    private $name = null;

    /**
     * @var null|string
     */
    private $createTime = null;

    /**
     * @var null|string
     */
    private $updateTime = null;

    public function __construct(string $json = null) {
        if (!is_null($json)) {
            $data = json_decode($json, true);
            $this->fields = $data;
            $this->name = $data['name'];
            $this->createTime = $data['createTime'];
            $this->updateTime = $data['updateTime'];
        }
    }

    public function getName() {
        return $this->name;
    }

    public function setString(string $fieldName, $value) {
        $this->fields[$fieldName] = [
            'stringValue' => $value
        ];
    }

    public function setDouble(string $fieldName, $value) {
        $this->fields[$fieldName] = [
            'doubleValue' => floatval($value)
        ];
    }

    public function setArray(string $fieldName, $value) {
        $this->fields[$fieldName] = [
            'arrayValue' => $value
        ];
    }

    public function setBoolean(string $fieldName, $value) {
        $this->fields[$fieldName] = [
            'booleanValue' => boolval($value)
        ];
    }

    public function setInteger(string $fieldName, $value) {
        $this->fields[$fieldName] = [
            'integerValue' => intval($value)
        ];
    }

    public function get(string $fieldName) {
        if (array_key_exists($fieldName, $this->fields)) {
            return reset($this->fields);
        }
        throw new \Exception('No such field');
    }

    public function toJson() {
        return json_encode([
            'fields' => $this->fields
        ]);
    }
}