<?php

namespace Svr\Core\Extensions\Herriot;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Svr\Core\Extensions\System\SystemFilter;
use Svr\Data\Models\DataAnimalsCodes;
use Svr\Data\Models\DataCompaniesObjects;
use Svr\Logs\Models\LogsHerriot;

/**
 * Класс для работы с API Хорриота
 * @version 0.1.6
 *
 * API-данные
 * vukemkuz-240202
 * bQ34tHHq4
 */
class ApiHerriot
{
	private string $api_url_domain_prod = 'https://api.vetrf.ru';
	private string $api_url_domain_test = 'https://api2.vetrf.ru:8002';
	private $api_url_domain = false;

    /**
     * Основной URL API
     */
    private array $api_url_list = [
        'directories_url'					=> '/platform/herriot/services/1.0/DictionaryService',
        'directories_url_countries'			=> '/platform/services/2.0/IkarService',
        'directories_url_organization'		=> '/platform/herriot/services/1.0/EnterpriseService',
        'register_animal'					=> '/platform/services/2.1/ApplicationManagementService',
        'check_register_animal'				=> '/platform/services/2.1/ApplicationManagementService',
    ];

    /**
     * Логин для запроса к Хорриоту
     * @var string
     */
    private $request_auth_login = false;
    /**
     * Пароль для запроса к Хорриоту
     * @var string
     */
    private $request_auth_password = false;

    /**
     * Данные для отправки запроса в Хорриот
     */
    private $request_data = false;

    /**
     * Конструктор
     */
    public function __construct($login, $password)
    {
        $this->request_auth_login       = trim($login);
        $this->request_auth_password	= trim($password);

        if (env('ENVIRONMENT') == 'PROD')
        {
            $this->api_url_domain       = $this->api_url_domain_prod;
        } else {
            $this->api_url_domain       = $this->api_url_domain_test;
        }
    }

    /**
     * Устанавливаем данные запроса
     * @param $data
     * @return void
     */
    public function requestRawData($data): void
    {
        $this->request_data         = $data;
    }

    /**
     * Отправляем запрос
     * @throws ConnectionException
     */
    public function requestSend($url): string
    {
        // Basic HTTP-аутентификация...
        $response = Http::withBasicAuth($this->request_auth_login, $this->request_auth_password)->withBody($this->request_data)->post($url);

        if ($response->status() == 200)
        {
            return $response->body();
        }
        else
        {
            return false;
        }
    }

	static function errorsList($type)
	{
		$types = [
			'html'							=> ['401', '404', 'timed_out'],
			'xml'							=> ['APPL01011', 'APPL01007', 'APPL02009', 'APLM0007', 'APLM0012',
												'HRRT101022004', 'HRRT101000001', 'HRRT101011004', 'HRRT101017012',
												'HRRT101017013', 'HRRT101016002', 'HRRT101017016', 'HRRT101010083',
												'HRRT101017006', 'HRRT101023001', 'HRRT101017010', 'HRRT101016001',
												'HRRT101043002', 'HRRT101018038', 'HRRT101010023'],
			'unknown'						=> []
		];

		return $types[$type];
	}

    /**
     * Получение справочника
     * @throws ConnectionException
     */
	public function getDirectory($directory_name)
    {
		$this->requestRawData('
		<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
		    xmlns:ws="http://api.vetrf.ru/schema/cdm/registry/ws-definitions/v2"
		    xmlns:bs="http://api.vetrf.ru/schema/cdm/base"
		    xmlns:dt="http://api.vetrf.ru/schema/cdm/dictionary/v2"
        >
		    <soapenv:Header/>
		    <soapenv:Body>
		        <ws:'.$directory_name.'>
                    <bs:listOptions>
                        <bs:count>1000</bs:count>
                        <bs:offset>0</bs:offset>
                    </bs:listOptions>
                </ws:'.$directory_name.'>
            </soapenv:Body>
        </soapenv:Envelope>');

        return $this->requestSend($this->api_url_domain.$this->api_url_list['directories_url'], 'RAW');
    }

    /**
     * Получение справочника стран
     * @throws ConnectionException
     */
	public function getDirectoryCountries($directory_name)
	{
		$this->requestRawData('
		<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
		    xmlns:ws="http://api.vetrf.ru/schema/cdm/registry/ws-definitions/v2"
		    xmlns:bs="http://api.vetrf.ru/schema/cdm/base"
		    xmlns:dt="http://api.vetrf.ru/schema/cdm/dictionary/v2"
        >
            <soapenv:Header/>
            <soapenv:Body>
                <ws:'.$directory_name.'>
                    <bs:listOptions>
                        <bs:count>1000</bs:count>
                        <bs:offset>0</bs:offset>
                    </bs:listOptions>
                </ws:'.$directory_name.'>
            </soapenv:Body>
        </soapenv:Envelope>');

        return $this->requestSend($this->api_url_domain.$this->api_url_list['directories_url_countries']);
	}

    /**
     * Получение данных хозяйствующего субъекта (компании) по ИНН
     * @throws ConnectionException
     */
	public function getDirectoryOrganizationByInn($inn)
	{
		$this->requestRawData('
		<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
			xmlns:ws="http://api.vetrf.ru/schema/cdm/registry/ws-definitions/v2"
		  	xmlns:bs="http://api.vetrf.ru/schema/cdm/base"
		 	xmlns:dt="http://api.vetrf.ru/schema/cdm/dictionary/v2">
   		<soapenv:Header/>
   		<soapenv:Body>
			<ws:getBusinessEntityListRequest>
				<bs:listOptions>
					<bs:count>1000</bs:count>
					<bs:offset>0</bs:offset>
				</bs:listOptions>
				<dt:businessEntity>
					<dt:inn>'.$inn.'</dt:inn>
				</dt:businessEntity>
			</ws:getBusinessEntityListRequest>
    	</soapenv:Body>
		</soapenv:Envelope>');

        return $this->requestSend($this->api_url_domain.$this->api_url_list['directories_url_organization']);
	}

    /**
     * Получение поднадзорных объектов хозяйствующего субъекта (компании) по ГУИД
     * @throws ConnectionException
     */
	public function getCompanyObjectsByGuid($guid, $count = 1000, $offset = 0)
	{
		$this->requestRawData('
		<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
			xmlns:ws="http://api.vetrf.ru/schema/cdm/registry/ws-definitions/v2"
		  	xmlns:bs="http://api.vetrf.ru/schema/cdm/base"
		  	xmlns:dt="http://api.vetrf.ru/schema/cdm/dictionary/v2">
   			<soapenv:Header/>
   			<soapenv:Body>
    			<ws:getBESupervisedObjectListRequest>
        			<bs:listOptions>
            			<bs:count>'.$count.'</bs:count>
            			<bs:offset>'.$offset.'</bs:offset>
        			</bs:listOptions>
        			<dt:businessEntity>
            			<bs:guid>'.$guid.'</bs:guid>
        			</dt:businessEntity>
    			</ws:getBESupervisedObjectListRequest>
    		</soapenv:Body>
		</soapenv:Envelope>');

        return $this->requestSend($this->api_url_domain.$this->api_url_list['directories_url_organization']);
	}

    /**
     * Отправляем животное на регистрацию
     * @param $animal_data
     * @param $herriot_web_login
     * @param $apikey
     * @param $issuerid
     * @param $serviceid
     * @return string
     * @throws ConnectionException
     */
	public function sendAnimal($animal_data, $herriot_web_login, $apikey, $issuerid, $serviceid)
	{
		$apiKey 	= $apikey;
		$issuerId 	= $issuerid;
		$login 		= $herriot_web_login;

		$reasonRegistration = 'OTHER';

		$request_raw_data = '
		<?xml version="1.0" encoding="UTF-8"?>
            <SOAP-ENV:Envelope
                xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
                xmlns:apl="http://api.vetrf.ru/schema/cdm/application"
                xmlns:apldef="http://api.vetrf.ru/schema/cdm/application/ws-definitions">
                <SOAP-ENV:Header/>
                <SOAP-ENV:Body>
                    <apldef:submitApplicationRequest>
                        <apldef:apiKey>'.$apiKey.'</apldef:apiKey>
                        <apl:application>
                            <apl:serviceId>'.$serviceid.'</apl:serviceId>
                            <apl:issuerId>'.$issuerId.'</apl:issuerId>
                            <apl:issueDate>'.date('Y-m-d').'T'.date('H:i:s').'</apl:issueDate>
                            <apl:data>
                                <hrt:registerAnimalRequest
                                    xmlns:dt="http://api.vetrf.ru/schema/cdm/dictionary/v2"
                                    xmlns:bs="http://api.vetrf.ru/schema/cdm/base"
                                    xmlns:hrt="http://api.vetrf.ru/schema/cdm/herriot/applications/v1"
                                    xmlns:vd="http://api.vetrf.ru/schema/cdm/mercury/vet-document/v2">
                                    <hrt:localTransactionId>'.$animal_data['animal_guid_self'].'</hrt:localTransactionId>
                                    <hrt:initiator><vd:login>'.$login.'</vd:login></hrt:initiator>
                                    <hrt:animalRegistration>
                                        <vd:identityType>INDIVIDUAL</vd:identityType>
                                        <vd:registrationStatus>ACTIVE</vd:registrationStatus>
                                        <vd:initialIdentificationType>'.$reasonRegistration.'</vd:initialIdentificationType>
                                        <vd:specifiedAnimal>
                                            <dt:species>
                                                <bs:guid>'.$animal_data['animal_specie_guid_horriot'].'</bs:guid>
                                            </dt:species>
                                            <dt:breed>
                                                <bs:guid>'.$animal_data['animal_breed_guid_horriot'].'</bs:guid>
                                            </dt:breed>
                                            <dt:colour>
                                                <dt:name>'.$animal_data['animal_colour'].'</dt:name>
                                            </dt:colour>
                                            <dt:gender>'.$animal_data['animal_gender_value_horriot'].'</dt:gender>
                                            <dt:name>'.$animal_data['animal_name_value'].'</dt:name>
                                            <dt:birthDate>
                                                <bs:year>'.date('Y', strtotime($animal_data['animal_date_birth'])).'</bs:year>
                                                <bs:month>'.date('m', strtotime($animal_data['animal_date_birth'])).'</bs:month>
                                                <bs:day>'.date('d', strtotime($animal_data['animal_date_birth'])).'</bs:day>
                                            </dt:birthDate>';
		if (!empty($animal_data['birth_object_guid_horriot']))
		{
			$request_raw_data .= '<dt:birthLocation>
                                    <dt:supervisedObject>
                                        <bs:guid>'.$animal_data['birth_object_guid_horriot'].'</bs:guid>
                                    </dt:supervisedObject>
                                </dt:birthLocation>';
		}

		$request_raw_data .= '</vd:specifiedAnimal>';

		$animal_mark_data = DataAnimalsCodes::animalMarkData($animal_data['animal_id']);
		if ($animal_mark_data !== false && count($animal_mark_data) > 0)
		{
			foreach ($animal_mark_data as $mark)
			{
				if (!empty(trim($mark['code_tool_type_id'])) && !empty(trim($mark['code_tool_date_set'])))
				{
					$request_raw_data .='
						<vd:specifiedAnimalIdentity>
							<vd:attachedLabel>
								<dt:animalID format="'.(($mark['mark_type_value_horriot'] == 'rshn') ? 'UNMM' : 'OTHER').'">'.$mark['code_value'].'</dt:animalID>
								<dt:type>'.(strtoupper($mark['mark_status_value_horriot'])).'</dt:type>
								<dt:markingMeans>
									<dt:type>'.(strtoupper($mark['mark_tool_type_value_horriot'])).'</dt:type>
								</dt:markingMeans>
								<dt:attachmentLocation>
									<bs:guid>'.$mark['tool_location_guid_horriot'].'</bs:guid>
								</dt:attachmentLocation>
							</vd:attachedLabel>
							<vd:associatedMarkingEvent>
								<vd:type>AME</vd:type>
								<vd:actualDate>
									<bs:date>
										<bs:year>'.date('Y', strtotime($mark['code_tool_date_set'])).'</bs:year>
										<bs:month>'.date('n', strtotime($mark['code_tool_date_set'])).'</bs:month>
										<bs:day>'.date('j', strtotime($mark['code_tool_date_set'])).'</bs:day>
									</bs:date>
								</vd:actualDate>
								<vd:operatorBusinessEntity>
									<bs:guid>'.$animal_data['animal_owner_company_guid_vetis'].'</bs:guid>
								</vd:operatorBusinessEntity>
							</vd:associatedMarkingEvent>
						</vd:specifiedAnimalIdentity>';
				}
			}
		}

		$request_raw_data .='
                            <vd:keepingDetails>
                                <vd:operatorSupervisedObject>
                                    <bs:guid>'.$animal_data['keeping_object_guid_horriot'].'</bs:guid>
                                </vd:operatorSupervisedObject>
                                <vd:keepingType>
                                    <bs:guid>'.$animal_data['animal_keeping_type_guid_horriot'].'</bs:guid>
                                </vd:keepingType>
                                <vd:keepingPurpose>
                                    <bs:guid>'.$animal_data['animal_keeping_purpose_guid_horriot'].'</bs:guid>
                                </vd:keepingPurpose>
                            </vd:keepingDetails>
                            <vd:breedingValueType>'.$animal_data['animal_breeding_value'].'</vd:breedingValueType>
                            <vd:referencedDocument>
                                <bs:uuid>'.$animal_data['animal_guid_self'].'</bs:uuid>
                                <vd:type>55</vd:type>
                                <vd:relationshipType>6</vd:relationshipType>
                            </vd:referencedDocument>
                        </hrt:animalRegistration>
                    </hrt:registerAnimalRequest>
                </apl:data>
            </apl:application>
        </apldef:submitApplicationRequest>
    </SOAP-ENV:Body>
</SOAP-ENV:Envelope>';

        DB::table(LogsHerriot::getTableName())
            ->insert(['application_request_herriot' => $request_raw_data, 'application_animal_id' => $animal_data['application_animal_id']]);


		$this->requestRawData($request_raw_data);

        return $this->requestSend($this->api_url_domain.$this->api_url_list['register_animal']);
	}

    /**
     * Проверяем статус заявки на регистрацию животного
     * @param $application_herriot_application_id
     * @param $apikey
     * @param $issuerid
     * @param $application_animal_id
     * @return string
     * @throws ConnectionException
     */
	public function checkSendAnimal($application_herriot_application_id, $apikey, $issuerid, $application_animal_id)
	{
		$apiKey 	= $apikey;
		$issuerId 	= $issuerid;

		$request_raw_data = '<?xml version="1.0" encoding="UTF-8"?>
								<SOAP-ENV:Envelope
										xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
										xmlns:apl="http://api.vetrf.ru/schema/cdm/application"
										xmlns:apldef="http://api.vetrf.ru/schema/cdm/application/ws-definitions">
									<SOAP-ENV:Header/>
									<SOAP-ENV:Body>
										<apldef:receiveApplicationResultRequest>
											<apldef:apiKey>'.$apiKey.'</apldef:apiKey>
											<apldef:issuerId>'.$issuerId.'</apldef:issuerId>
											<apldef:applicationId>'.$application_herriot_application_id.'</apldef:applicationId>
										</apldef:receiveApplicationResultRequest>
									</SOAP-ENV:Body>
								</SOAP-ENV:Envelope>';

        DB::table(LogsHerriot::getTableName())->where('application_animal_id', '=', $application_animal_id)->update(['application_request_application_herriot' => $request_raw_data]);

		$this->requestRawData($request_raw_data);

        return $this->requestSend($this->api_url_domain.$this->api_url_list['check_register_animal']);
	}

    /**
     * Парсим ответ от хорриота с целью поиска ошибок
     * @param $error_string
     * @return array
     */
	public static function errorParser($error_string)
	{
		$error_data['error_type']				= 'unknown';
		$error_data['error_status']				= false;
		$error_data['error_message']			= [];

		if (mb_stripos($error_string, 'timed out'))
		{
			$error_data['error_type'] 			= 'html';
			$error_data['error_code']			= 'timed_out';
			$error_data['error_status']			= true;
			$error_data['error_message'][]		= 'Ошибка таймаута при обращение к Хорриот';
		}else{
			if(mb_stripos($error_string, '<HTML>'))
			{
				$error_data['error_type']			= 'html';
			}else{
				$error_data['error_type']			= 'xml';
			}

			$errors_list							= self::errorsList($error_data['error_type']);

			foreach($errors_list as $item)
			{
				if(mb_stripos($error_string, $item))
				{
					$error_data['error_code']		= $item;
					$error_data['error_status']		= true;

					$error_data['error_message'][] 	= self::extractErrorText($error_string, $error_data['error_type'], $error_data['error_code']);
				}
			}
		}

		if(count($error_data['error_message']) > 0)
		{
			if(count($error_data['error_message']) > 1)
			{
				$error_data['error_message']			= implode('; ', $error_data['error_message']);
			}else{
				$error_data['error_message']			= (string)$error_data['error_message'][0];
			}
		}else{
			$error_data['error_message']				= '';
		}


		return $error_data;
	}

    /**
     * Запускаем функцию обработки ошибки из хорриота
     * @param $error_data
     * @param $error_type
     * @param $error_code
     * @return string
     */
	static function extractErrorText($error_data, $error_type, $error_code)
	{
		if($error_code == 'unknown')
		{
			return  '';
		}

		$method_name		= 'error_'.$error_type.'_'.$error_code;

		if(method_exists('ApiHorriot', $method_name))
		{
			return self::{$method_name}($error_data);
		}

		return  '';
	}

	/**
	 * Неправильные реквизиты от апи Хорриот у пользователя
	 */
	static function error_html_401($error_data)
	{
		return 'Введены неправильные Логин Хорриот и/или пароль Хорриот';
	}

	/**
	 * Неправильный url апи Хорриот
	 */
	static function error_html_404($error_data)
	{
		return 'Неверно указан url интеграционного шлюза Хорриот. Обратитесь в техническую поддержку Плинор';
	}

	/**
	 * Неправильный url апи Хорриот
	 */
	static function error_html_timed_out($error_data)
	{
		return 'Ошибка таймаута при обращение к Хорриот';
	}

	/**
	 * Указан некорректный ключ доступа к интеграционному шлюзу (Неправильный apiKey у пользователя)
	 */
	static function error_xml_APPL01011($error_data)
	{
		preg_match('/<bs:error code="APPL01011">(.*?)<\/bs:error>/s', $error_data, $matches);

		return $matches[1].' (APIKey)';
	}

	/**
	 * Идентификатор заявителя (issuerId) не соответсвует установленному формату. (Неправильный issuerId у пользователя)
	 */
	static function error_xml_APPL01007($error_data)
	{
		preg_match('/<bs:error code="APPL01007">(.*?)<\/bs:error>/s', $error_data, $matches);

		return $matches[1];
	}

	/**
	 * Идентификатор заявки обязателен для заполнения (Не указан идентификатор заявки)
	 */
	static function error_xml_APPL02009($error_data)
	{
		preg_match('/<bs:error code="APPL02009">(.*?)<\/bs:error>/s', $error_data, $matches);

		return $matches[1];
	}

	/**
	 * Wrong application data format. Format validation failed due to XML Schema rules: At line: 4 column: 75. cvc-datatype-valid.1.2.1: 'tatyana.kuimova@agro78.ru' is not a valid value for 'NCName'. cvc-type.3.1.3: The value 'tatyana.kuimova@agro78.ru' of element 'vd:login' is not valid.
	 */
	static function error_xml_APLM0007($error_data)
	{
		return 'Неверный формат учетный записи Хорриот';
	}

	/**
	 * An unexpected error has occurred while invoking target service operation
	 */
	static function error_xml_APLM0012($error_data)
	{
		return 'Неизвестная ошибка в API Хорриот';
	}

	/**
	 * Пользователь с логином ... не найден (неправильный логин от веб морды)
	 */
	static function error_xml_HRRT101022004($error_data)
	{
		preg_match('/<apl:error code="HRRT101022004">(.*?)<\/apl:error>/s', $error_data, $matches);

		return $matches[1];
	}

	/**
	 * Отсутствует доступ к поднадзорному объекту (указано неправильное место содержания (и мб рождения) животного)
	 */
	static function error_xml_HRRT101000001($error_data)
	{
		preg_match('/<apl:error code="HRRT101000001">(.*?)<\/apl:error>/s', $error_data, $matches);

		$error_string = $matches[1];

		$object_guid = SystemFilter::mb_str_replace(['Отсутствует доступ к поднадзорному объекту/региону с глобальным идентификатором '], [''], $error_string);
		if (!empty($object_guid))
		{
			$object_info = (new DataCompaniesObjects())->find('company_object_guid_horriot', '=', $object_guid)->toArray();
			if (!empty($object_info))
			{
				$error_string .= '('.$object_info['company_object_approval_number'].', '.$object_info['company_object_address_view'].')';
			}
		}

		return $error_string;
	}

	/**
	 * Поднадзорный объект с идентификатором *** не обслуживается
	 */
	static function error_xml_HRRT101043002($error_data)
	{
		preg_match('/<apl:error code="HRRT101043002">(.*?)<\/apl:error>/s', $error_data, $matches);

		$error_string = $matches[1];

		$object_guid = SystemFilter::mb_str_replace(['Поднадзорный объект с идентификатором ', ' не обслуживается: некорректные виды деятельности объекта'], [''], $error_string);
		if (!empty($object_guid))
		{
			$object_info = (new DataCompaniesObjects())->find('company_object_guid_horriot', '=', $object_guid)->toArray();
            if (!empty($object_info))
			{
				$error_string .= '('.$object_info['company_object_approval_number'].', '.$object_info['company_object_address_view'].')';
			}
		}

		return $error_string;
	}

	/**
	 * Место содержания животного/группы животных с идентификатором d06fa215-f2be-441e-a545-a5d75088d67f не найдено
	 */
	static function error_xml_HRRT101018038($error_data)
	{
		preg_match('/<apl:error code="HRRT101018038">(.*?)<\/apl:error>/s', $error_data, $matches);

		$error_string = $matches[1];

		$object_guid = SystemFilter::mb_str_replace(['Место содержания животного/группы животных с идентификатором ', ' не найдено'], [''], $error_string);
		if (!empty($object_guid))
		{
            $object_info = (new DataCompaniesObjects())->find('company_object_guid_horriot', '=', $object_guid)->toArray();
            if (!empty($object_info))
			{
				$error_string .= '('.$object_info['company_object_approval_number'].', '.$object_info['company_object_address_view'].')';
			}
		}

		return $error_string;
	}

	/**
	 * Животное/группа животных с такими данными уже зарегистрирована
	 */
	static function error_xml_HRRT101011004($error_data)
	{
		//TODO: ТУТ НАДО ДОПОЛНИТЕЛЬНО ПОЛУЧИТЬ ИНФОРМАЦИЮ О ЖИВОТНОМ ИБО СООБЩЕНИЕ ВЫГЛДЯИТ ВОТ ТАК ВОТ:
		/*
		 * Животное/группа животных с такими данными уже зарегистрирована (guid: 6f289ee7-797d-49db-be24-02cf1c5f67d8)
		 */
		preg_match('/<apl:error code="HRRT101011004">(.*?)<\/apl:error>/s', $error_data, $matches);

		return $matches[1];
	}

	/**
	 * Контрольная сумма номера средства маркирования RU154161523 указана неверно. Проверьте правильность ввода номера
	 */
	static function error_xml_HRRT101017012($error_data)
	{
		preg_match('/<apl:error code="HRRT101017012">(.*?)<\/apl:error>/s', $error_data, $matches);

		return $matches[1];
	}

	/**
	 * Номер средства маркирования RU1539*4951 должен соответствовать формату RU1XXXXXXXX
	 */
	static function error_xml_HRRT101017013($error_data)
	{
		preg_match('/<apl:error code="HRRT101017013">(.*?)<\/apl:error>/s', $error_data, $matches);

		return $matches[1];
	}

	/**
	 * Номер(а) средства маркирования RU106478119 еще не выпущен
	 */
	static function error_xml_HRRT101016002($error_data)
	{
		preg_match('/<apl:error code="HRRT101016002">(.*?)<\/apl:error>/s', $error_data, $matches);

		return $matches[1];
	}

	/**
	 * Основное средство маркирования должно иметь формат номера по правилам идентификации
	 */
	static function error_xml_HRRT101017016($error_data)
	{
		preg_match('/<apl:error code="HRRT101017016">(.*?)<\/apl:error>/s', $error_data, $matches);

		return $matches[1];
	}

	/**
	 * Допускается не более 2 активных средств маркирования с категорией "Основное"
	 */
	static function error_xml_HRRT101010083($error_data)
	{
		preg_match('/<apl:error code="HRRT101010083">(.*?)<\/apl:error>/s', $error_data, $matches);

		return $matches[1];
	}

	/**
	 * Тип средства маркирования TATTOO не применим для категории MAIN
	 */
	static function error_xml_HRRT101017006($error_data)
	{
		preg_match('/<apl:error code="HRRT101017006">(.*?)<\/apl:error>/s', $error_data, $matches);

		return $matches[1];
	}

	/**
	 * Должно быть указано хотя бы одно активное основное средство маркирования
	 */
	static function error_xml_HRRT101023001($error_data)
	{
		preg_match('/<apl:error code="HRRT101023001">(.*?)<\/apl:error>/s', $error_data, $matches);

		return $matches[1];
	}

	/**
	 * Тип средства маркирования TATTOO не применим для категории MAIN
	 */
	static function error_xml_HRRT101017010($error_data)
	{
		preg_match('/<apl:error code="HRRT101017010">(.*?)<\/apl:error>/s', $error_data, $matches);

		return $matches[1];
	}

	/**
	 * Номер(а) средства маркирования RU104304820 уже используется
	 */
	static function error_xml_HRRT101016001($error_data)
	{
		preg_match('/<apl:error code="HRRT101016001">(.*?)<\/apl:error>/s', $error_data, $matches);

		return $matches[1];
	}

	/**
	 * Дата рождения должна быть не больше текущей даты
	 */
	static function error_xml_HRRT101010023($error_data)
	{
		preg_match('/<apl:error code="HRRT101010023">(.*?)<\/apl:error>/s', $error_data, $matches);

		return $matches[1];
	}


}
