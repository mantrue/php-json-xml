<?php
namespace App\Resource;
class Api {
		/**
		*按json 或者xml 方式输出通信数据
		*@param interger $code 状态码
		*@param string $message 提示信息
		*@param array $data 数据
		*@param string 数据类型
		*@return 根据参数返回数据，默认json
		*@auth 承鹏辉
		*@email penghui219@163.com
		*/
		const JSONTYPE = "json"; //json 数据
		const XMLTYPE = "xml";  //xml数据
		const ARRAYTYPE = "array"; //debug调试数组 (备注:如果想生成什么数据继续追加)
		
		const CODETYPE = "code";  //返回生成数据下标 状态码
		const MESSAGETYPE = "message";//返回生成数据下标 提示信息
		const DATATYPE = "data";//返回生成数据下标  数据
		const DEBUGDATA = "调用错误请检查参数"; //调用错误信息
		
		
		/**
		*按json 或者xml 方式输出通信数据
		*@param interger $code 状态码
		*@param string $message 提示信息
		*@param array $data 数据
		*@param string 数据类型
		*@return 根据参数返回数据，默认json
		*@auth 承鹏辉
		*@email penghui219@163.com
		*/
		
		public static function interfaceData ( $code, $message='', $data, $type=self::JSONTYPE ) {
			if (!is_numeric($code) || !is_array($data)) {
				return self::DEBUGDATA;
			}
			//debug模式打印用的数据
			$result = array(
				self::CODETYPE => $code,
				self::MESSAGETYPE => $message,
				self::DATATYPE => $data,
			);
			
			switch ($type) {
				case self::JSONTYPE:
					$data = self::jsonEncode( $code, $message, $data );
					break;
				case self::ARRAYTYPE:
					echo "<pre>";
					$data = $result;
					break;
				case self::XMLTYPE:
					$data = self::xmlEncode( $code, $message, $data );
					break;
				default:
					self::UserError(500,'error','Please check the URL, determine the parameters are correct');
			}
			
			return $data;
		}
		/**
		*按json方式输出通信数据
		*@param interger $code 状态码
		*@param string $message 提示信息
		*@param array $data 数据
		*@auth 承鹏辉
		*@email penghui219@163.com
		*@return json
		*/
		public static function jsonEncode (	$code, $message='', $data ) {
			
			if (!is_numeric($code) || !is_array($data)) {
				return false;
			}
			$result = array(
				self::CODETYPE => $code,
				self::MESSAGETYPE => $message,
				self::DATATYPE => $data,
			);
			return json_encode($result);
		}
		
		/**
		*按xml方式输出通信数据
		*@param interger $code 状态码
		*@param string $message 提示信息
		*@param array $data 数据
		*@auth 承鹏辉
		*@email penghui219@163.com
		*@return json
		*/
		public static function xmlEncode ( $code, $message='', $data ) {
			if (!is_numeric($code) || !is_array($data)) {
				return false;
			}
			$result = array(
				self::CODETYPE => $code,
				self::MESSAGETYPE => $message,
				self::DATATYPE => $data,
			);
			header("Content-Type:text/xml");
			$xml  = "<?xml version='1.0' encoding='UTF-8'?>\n";
			$xml .= "<document>\n";
			$xml .= self::xmlToEncode($result);
			$xml .= "</document>";
			return $xml;
		}
		public static function xmlToEncode ($result) {
			$xml = $attr = "";
			foreach ($result as $key => $value) {
				if (is_numeric($key)) {
					$attr = " id='{$key}'";
					$key = "item";
				}
				$xml .= "<{$key}{$attr}>";
				$xml .= is_array($value)?self::xmlToEncode($value):$value;
				$xml .= "</{$key}>\n";
			}
			return $xml;
		}
		/*
			return 用户错误信息
		*/
		public static function UserError (	$code, $message='', $data ) {
			if (!is_numeric($code)) {
				return false;
			}
			$result = array(
				self::CODETYPE => $code,
				self::MESSAGETYPE => $message,
				self::DATATYPE => $data,
			);
			return json_encode($result);
		}
		
		/**
		*根据xml文件生成json数据
		*@param interger $code 状态码
		*@auth 承鹏辉
		*@email penghui219@163.com
		*@return json
		*/
		public static function xmlToArray($xml, $options = array()) {
			
			$defaults = array(
				'namespaceSeparator' => ':',
				'attributePrefix' => '@',   
				'alwaysArray' => array(),   
				'autoArray' => true,        
				'textContent' => '$',      
				'autoText' => true,         
				'keySearch' => false,       
				'keyReplace' => false
			);
			$options = array_merge($defaults, $options);
			$namespaces = $xml->getDocNamespaces();
			$namespaces[''] = null;
			
			$attributesArray = array();
			foreach ($namespaces as $prefix => $namespace) {
				foreach ($xml->attributes($namespace) as $attributeName => $attribute) {
					
					if ($options['keySearch']) 
						$attributeName = str_replace($options['keySearch'], $options['keyReplace'], $attributeName);
						$attributeKey = $options['attributePrefix']. ($prefix ? $prefix . $options['namespaceSeparator'] : ''). $attributeName;
						$attributesArray[$attributeKey] = (string)$attribute;
				}
			}
			$tagsArray = array();
			foreach ($namespaces as $prefix => $namespace) {
				foreach ($xml->children($namespace) as $childXml) {
					
					$childArray = Api::xmlToArray($childXml, $options);
					list($childTagName, $childProperties) = each($childArray);
					
					if ($options['keySearch']) $childTagName = str_replace($options['keySearch'], $options['keyReplace'], $childTagName);
					
					if ($prefix) $childTagName = $prefix . $options['namespaceSeparator'] . $childTagName;
		 
					if (!isset($tagsArray[$childTagName])) {
						
						$tagsArray[$childTagName] = in_array($childTagName, $options['alwaysArray']) || !$options['autoArray'] ? array($childProperties) : $childProperties;
					} elseif (
						is_array($tagsArray[$childTagName]) && array_keys($tagsArray[$childTagName]) === range(0, count($tagsArray[$childTagName]) - 1)
					) {
						
						$tagsArray[$childTagName][] = $childProperties;
					} else {
						
						$tagsArray[$childTagName] = array($tagsArray[$childTagName], $childProperties);
					}
				}
			}
			
			$textContentArray = array();
			$plainText = trim((string)$xml);
			if ($plainText !== '') $textContentArray[$options['textContent']] = $plainText;
			$propertiesArray = !$options['autoText'] || $attributesArray || $tagsArray || ($plainText === '') ? array_merge($attributesArray, $tagsArray, $textContentArray) : $plainText;
			
			return array(
				$xml->getName() => $propertiesArray
			);
		}
		
	}