<?php
/**
 * Copyright 2023 LINE Corporation
 *
 * LINE Corporation licenses this file to you under the Apache License,
 * version 2.0 (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at:
 *
 *   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */
/**
 * GetMessageEventResponseOverview
 *
 * PHP version 7.4
 *
 * @category Class
 * @package  LINE\Clients\Insight
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */

/**
 * LINE Messaging API(Insight)
 *
 * This document describes LINE Messaging API(Insight).
 *
 * The version of the OpenAPI document: 0.0.1
 * Generated by: https://openapi-generator.tech
 * OpenAPI Generator version: 6.6.0
 */

/**
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace LINE\Clients\Insight\Model;

use \ArrayAccess;
use \LINE\Clients\Insight\ObjectSerializer;

/**
 * GetMessageEventResponseOverview Class Doc Comment
 *
 * @category Class
 * @description Summary of message statistics.
 * @package  LINE\Clients\Insight
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class GetMessageEventResponseOverview implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'GetMessageEventResponseOverview';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'requestId' => 'string',
        'timestamp' => 'int',
        'delivered' => 'int',
        'uniqueImpression' => 'int',
        'uniqueClick' => 'int',
        'uniqueMediaPlayed' => 'int',
        'uniqueMediaPlayed100Percent' => 'int'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'requestId' => null,
        'timestamp' => 'int64',
        'delivered' => 'int64',
        'uniqueImpression' => 'int64',
        'uniqueClick' => 'int64',
        'uniqueMediaPlayed' => 'int64',
        'uniqueMediaPlayed100Percent' => 'int64'
    ];

    /**
      * Array of nullable properties. Used for (de)serialization
      *
      * @var boolean[]
      */
    protected static array $openAPINullables = [
        'requestId' => false,
		'timestamp' => false,
		'delivered' => false,
		'uniqueImpression' => true,
		'uniqueClick' => true,
		'uniqueMediaPlayed' => true,
		'uniqueMediaPlayed100Percent' => true
    ];

    /**
      * If a nullable field gets set to null, insert it here
      *
      * @var boolean[]
      */
    protected array $openAPINullablesSetToNull = [];

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPITypes()
    {
        return self::$openAPITypes;
    }

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPIFormats()
    {
        return self::$openAPIFormats;
    }

    /**
     * Array of nullable properties
     *
     * @return array
     */
    protected static function openAPINullables(): array
    {
        return self::$openAPINullables;
    }

    /**
     * Array of nullable field names deliberately set to null
     *
     * @return boolean[]
     */
    private function getOpenAPINullablesSetToNull(): array
    {
        return $this->openAPINullablesSetToNull;
    }

    /**
     * Setter - Array of nullable field names deliberately set to null
     *
     * @param boolean[] $openAPINullablesSetToNull
     */
    private function setOpenAPINullablesSetToNull(array $openAPINullablesSetToNull): void
    {
        $this->openAPINullablesSetToNull = $openAPINullablesSetToNull;
    }

    /**
     * Checks if a property is nullable
     *
     * @param string $property
     * @return bool
     */
    public static function isNullable(string $property): bool
    {
        return self::openAPINullables()[$property] ?? false;
    }

    /**
     * Checks if a nullable property is set to null.
     *
     * @param string $property
     * @return bool
     */
    public function isNullableSetToNull(string $property): bool
    {
        return in_array($property, $this->getOpenAPINullablesSetToNull(), true);
    }

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'requestId' => 'requestId',
        'timestamp' => 'timestamp',
        'delivered' => 'delivered',
        'uniqueImpression' => 'uniqueImpression',
        'uniqueClick' => 'uniqueClick',
        'uniqueMediaPlayed' => 'uniqueMediaPlayed',
        'uniqueMediaPlayed100Percent' => 'uniqueMediaPlayed100Percent'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'requestId' => 'setRequestId',
        'timestamp' => 'setTimestamp',
        'delivered' => 'setDelivered',
        'uniqueImpression' => 'setUniqueImpression',
        'uniqueClick' => 'setUniqueClick',
        'uniqueMediaPlayed' => 'setUniqueMediaPlayed',
        'uniqueMediaPlayed100Percent' => 'setUniqueMediaPlayed100Percent'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'requestId' => 'getRequestId',
        'timestamp' => 'getTimestamp',
        'delivered' => 'getDelivered',
        'uniqueImpression' => 'getUniqueImpression',
        'uniqueClick' => 'getUniqueClick',
        'uniqueMediaPlayed' => 'getUniqueMediaPlayed',
        'uniqueMediaPlayed100Percent' => 'getUniqueMediaPlayed100Percent'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @return array
     */
    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @return array
     */
    public static function setters()
    {
        return self::$setters;
    }

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @return array
     */
    public static function getters()
    {
        return self::$getters;
    }

    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName()
    {
        return self::$openAPIModelName;
    }


    /**
     * Associative array for storing property values
     *
     * @var mixed[]
     */
    protected $container = [];

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values
     *                      initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->setIfExists('requestId', $data ?? [], null);
        $this->setIfExists('timestamp', $data ?? [], null);
        $this->setIfExists('delivered', $data ?? [], null);
        $this->setIfExists('uniqueImpression', $data ?? [], null);
        $this->setIfExists('uniqueClick', $data ?? [], null);
        $this->setIfExists('uniqueMediaPlayed', $data ?? [], null);
        $this->setIfExists('uniqueMediaPlayed100Percent', $data ?? [], null);
    }

    /**
    * Sets $this->container[$variableName] to the given data or to the given default Value; if $variableName
    * is nullable and its value is set to null in the $fields array, then mark it as "set to null" in the
    * $this->openAPINullablesSetToNull array
    *
    * @param string $variableName
    * @param array  $fields
    * @param mixed  $defaultValue
    */
    private function setIfExists(string $variableName, array $fields, $defaultValue): void
    {
        if (self::isNullable($variableName) && array_key_exists($variableName, $fields) && is_null($fields[$variableName])) {
            $this->openAPINullablesSetToNull[] = $variableName;
        }

        $this->container[$variableName] = $fields[$variableName] ?? $defaultValue;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        return $invalidProperties;
    }

    /**
     * Validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return count($this->listInvalidProperties()) === 0;
    }


    /**
     * Gets requestId
     *
     * @return string|null
     */
    public function getRequestId()
    {
        return $this->container['requestId'];
    }

    /**
     * Sets requestId
     *
     * @param string|null $requestId Request ID.
     *
     * @return self
     */
    public function setRequestId($requestId)
    {
        if (is_null($requestId)) {
            throw new \InvalidArgumentException('non-nullable requestId cannot be null');
        }
        $this->container['requestId'] = $requestId;

        return $this;
    }

    /**
     * Gets timestamp
     *
     * @return int|null
     */
    public function getTimestamp()
    {
        return $this->container['timestamp'];
    }

    /**
     * Sets timestamp
     *
     * @param int|null $timestamp UNIX timestamp for message delivery time in seconds.
     *
     * @return self
     */
    public function setTimestamp($timestamp)
    {
        if (is_null($timestamp)) {
            throw new \InvalidArgumentException('non-nullable timestamp cannot be null');
        }
        $this->container['timestamp'] = $timestamp;

        return $this;
    }

    /**
     * Gets delivered
     *
     * @return int|null
     */
    public function getDelivered()
    {
        return $this->container['delivered'];
    }

    /**
     * Sets delivered
     *
     * @param int|null $delivered Number of messages delivered. This property shows values of less than 20. However, if all messages have not been sent, it will be null.
     *
     * @return self
     */
    public function setDelivered($delivered)
    {
        if (is_null($delivered)) {
            throw new \InvalidArgumentException('non-nullable delivered cannot be null');
        }
        $this->container['delivered'] = $delivered;

        return $this;
    }

    /**
     * Gets uniqueImpression
     *
     * @return int|null
     */
    public function getUniqueImpression()
    {
        return $this->container['uniqueImpression'];
    }

    /**
     * Sets uniqueImpression
     *
     * @param int|null $uniqueImpression Number of users who opened the message, meaning they displayed at least 1 bubble.
     *
     * @return self
     */
    public function setUniqueImpression($uniqueImpression)
    {
        if (is_null($uniqueImpression)) {
            array_push($this->openAPINullablesSetToNull, 'uniqueImpression');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('uniqueImpression', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['uniqueImpression'] = $uniqueImpression;

        return $this;
    }

    /**
     * Gets uniqueClick
     *
     * @return int|null
     */
    public function getUniqueClick()
    {
        return $this->container['uniqueClick'];
    }

    /**
     * Sets uniqueClick
     *
     * @param int|null $uniqueClick Number of users who opened any URL in the message.
     *
     * @return self
     */
    public function setUniqueClick($uniqueClick)
    {
        if (is_null($uniqueClick)) {
            array_push($this->openAPINullablesSetToNull, 'uniqueClick');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('uniqueClick', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['uniqueClick'] = $uniqueClick;

        return $this;
    }

    /**
     * Gets uniqueMediaPlayed
     *
     * @return int|null
     */
    public function getUniqueMediaPlayed()
    {
        return $this->container['uniqueMediaPlayed'];
    }

    /**
     * Sets uniqueMediaPlayed
     *
     * @param int|null $uniqueMediaPlayed Number of users who started playing any video or audio in the message.
     *
     * @return self
     */
    public function setUniqueMediaPlayed($uniqueMediaPlayed)
    {
        if (is_null($uniqueMediaPlayed)) {
            array_push($this->openAPINullablesSetToNull, 'uniqueMediaPlayed');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('uniqueMediaPlayed', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['uniqueMediaPlayed'] = $uniqueMediaPlayed;

        return $this;
    }

    /**
     * Gets uniqueMediaPlayed100Percent
     *
     * @return int|null
     */
    public function getUniqueMediaPlayed100Percent()
    {
        return $this->container['uniqueMediaPlayed100Percent'];
    }

    /**
     * Sets uniqueMediaPlayed100Percent
     *
     * @param int|null $uniqueMediaPlayed100Percent Number of users who played the entirety of any video or audio in the message.
     *
     * @return self
     */
    public function setUniqueMediaPlayed100Percent($uniqueMediaPlayed100Percent)
    {
        if (is_null($uniqueMediaPlayed100Percent)) {
            array_push($this->openAPINullablesSetToNull, 'uniqueMediaPlayed100Percent');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('uniqueMediaPlayed100Percent', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['uniqueMediaPlayed100Percent'] = $uniqueMediaPlayed100Percent;

        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param integer $offset Offset
     *
     * @return boolean
     */
    public function offsetExists($offset): bool
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param integer $offset Offset
     *
     * @return mixed|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->container[$offset] ?? null;
    }

    /**
     * Sets value based on offset.
     *
     * @param int|null $offset Offset
     * @param mixed    $value  Value to be set
     *
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     *
     * @param integer $offset Offset
     *
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->container[$offset]);
    }

    /**
     * Serializes the object to a value that can be serialized natively by json_encode().
     * @link https://www.php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed Returns data which can be serialized by json_encode(), which is a value
     * of any type other than a resource.
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
       return ObjectSerializer::sanitizeForSerialization($this);
    }

    /**
     * Gets the string presentation of the object
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode(
            ObjectSerializer::sanitizeForSerialization($this),
            JSON_PRETTY_PRINT
        );
    }

    /**
     * Gets a header-safe presentation of the object
     *
     * @return string
     */
    public function toHeaderValue()
    {
        return json_encode(ObjectSerializer::sanitizeForSerialization($this));
    }
}


