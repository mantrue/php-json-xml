<?php
namespace App\Resource;
class Api {
		/**
		*��json ����xml ��ʽ���ͨ������
		*@param interger $code ״̬��
		*@param string $message ��ʾ��Ϣ
		*@param array $data ����
		*@param string ��������
		*@return ���ݲ����������ݣ�Ĭ��json
		*@auth ������
		*@email penghui219@163.com
		*/
		const JSONTYPE = "json"; //json ����
		const XMLTYPE = "xml";  //xml����
		const ARRAYTYPE = "array"; //debug�������� (��ע:���������ʲô���ݼ���׷��)
		
		const CODETYPE = "code";  //�������������±� ״̬��
		const MESSAGETYPE = "message";//�������������±� ��ʾ��Ϣ
		const DATATYPE = "data";//�������������±�  ����
		const DEBUGDATA = "���ô����������"; //���ô�����Ϣ
		
		
		/**
		*��json ����xml ��ʽ���ͨ������
		*@param interger $code ״̬��
		*@param string $message ��ʾ��Ϣ
		*@param array $data ����
		*@param string ��������
		*@return ���ݲ����������ݣ�Ĭ��json
		*@auth ������
		*@email penghui219@163.com
		*/
		
		public static function interfaceData ( $code, $message='', $data, $type=self::JSONTYPE ) {
			if (!is_numeric($code) || !is_array($data)) {
				return self::DEBUGDATA;
			}
			//debugģʽ��ӡ�õ�����
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
		*��json��ʽ���ͨ������
		*@param interger $code ״̬��
		*@param string $message ��ʾ��Ϣ
		*@param array $data ����
		*@auth ������
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
		*��xml��ʽ���ͨ������
		*@param interger $code ״̬��
		*@param string $message ��ʾ��Ϣ
		*@param array $data ����
		*@auth ������
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
			return �û�������Ϣ
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
		*����xml�ļ�����json����
		*@param interger $code ״̬��
		*@auth ������
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