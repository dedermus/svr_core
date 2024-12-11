<?php
namespace Svr\Core\Extensions\System;

/**
 * Системный класс обработки и фильтрации входящих данных.
 */
class SystemFilter
{
	/**
	 * Функция для сортировки массива с объектами
	 * @param       $array
	 * @param array $args
	 * @return mixed
	 */
    public static function sort_nested_arrays($array, $args = array('votes' => 'desc'))
	{
        usort($array, function($a, $b) use ( $args )
		{
			$res	= 0;
			$a		= (object)$a;
			$b		= (object)$b;

			foreach($args as $k => $v)
			{
				if($a->$k == $b->$k) continue;

				$res = ( $a->$k < $b->$k ) ? -1 : 1;

				if($v == 'desc')
				{
					$res= -$res;
				}

				break;
			}

			return $res;
		});

		return $array;
	}


	public static function is_valid_inn($inn)
	{
		if ( preg_match('/\D/', $inn) ) return false;

		$inn = (string)$inn;
		$len = strlen($inn);

		if ( $len === 10 )
		{
			return $inn[9] === (string) (((
				2*$inn[0] + 4*$inn[1] + 10*$inn[2] +
				3*$inn[3] + 5*$inn[4] +  9*$inn[5] +
				4*$inn[6] + 6*$inn[7] +  8*$inn[8]
			) % 11) % 10);
		}
		elseif ( $len === 12 )
		{
			$num10 = (string) (((
				 7*$inn[0] + 2*$inn[1] + 4*$inn[2] +
				10*$inn[3] + 3*$inn[4] + 5*$inn[5] +
				 9*$inn[6] + 4*$inn[7] + 6*$inn[8] +
				 8*$inn[9]
			) % 11) % 10);

			$num11 = (string) (((
				3*$inn[0] +  7*$inn[1] + 2*$inn[2] +
				4*$inn[3] + 10*$inn[4] + 3*$inn[5] +
				5*$inn[6] +  9*$inn[7] + 4*$inn[8] +
				6*$inn[9] +  8*$inn[10]
			) % 11) % 10);

			return $inn[11] === $num11 && $inn[10] === $num10;
		}

		return false;
	}


	/**
	 * Первичная чистка данных $_POST
	 * @param	string|array	$data			Входящие данные
	 * @return	string|array
	 */
	public static function strip_post($data)
	{
		if(is_array($data))
		{
			foreach ($data as $key => $value)
			{
				$data[$key] = self::strip_post($value);
			}
		}else{
			$data = trim($data);
		}

		return $data;
	}


	/**
	 * Первичная чистка данных $_GET
	 * @param	string|array	$data			Входящие данные
	 * @return	string|array
	 */
	public static function strip_get($data)
	{
		if(is_array($data))
		{
			foreach($data as $value)
			{
				self::strip_get($value);
			}
		}else{
			$data		= explode('?', $data);
			$quotes		= array ("\x27", "\x22", "\x60", "\t", "\n", "\r", "*", "<", ">", "?", "!" );	//"%",
			$data		= trim(strip_tags($data[0]));
			$data		= str_replace($quotes, '', $data);
		}

		return self::clean_url($data);
	}


	/**
	 * Достаем GET-параметры из строки запроса в виде массива
	 * @param	string	$url					Текст строки запроса
	 * @return	array
	 */
	public static function url_data($url)
	{
		$url_data		= array();
		$url_array		= parse_url($url);

		if($url_array && is_array($url_array) && isset($url_array['query']))
		{
			parse_str($url_array['query'], $url_data);

			return $url_data;
		}else{
			return false;
		}
	}


	public static function date_to_uts($date_str)
	{
		$tmp = explode(".", $date_str);

		return mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]);
	}


	/**
	 * Проверка на число
	 * @param	string	$value					Строка для проверки
	 * @return	int|boolean
	 */
	public static function is_num($value)
	{
		if(!is_array($value))
		{
			if(preg_match("/^[0-9]+$/", $value))
			{
				return $value;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}


	/**
	 * Очистка URL
	 * @param	string	$url					URL для проверки
	 * @return	string
	 */
	public static function clean_url($url)
	{
		$bad_entities	= array("&", "\"", "'", '\"', "\'", "<", ">", "(", ")", "*");
		$safe_entities	= array("&amp;", "", "", "", "", "", "", "", "", "");
		$url			= str_replace($bad_entities, $safe_entities, $url);

		return $url;
    }


	/**
	 * Проверка Email
	 * @param	string	$email					Строка для проверки
	 * @return	boolean|string
	 */
	public static function is_email($email)
	{
		if(!empty($email))
		{
			//if(preg_match("/^[a-z0-9_-]+([\.a-z0-9_-])*@[a-z0-9_-]((\.[a-z0-9_-])*[a-z0-9_-]+)*\.[a-z]{2,3}$/i",$email)){
			if(preg_match("|^[-0-9a-z_\.]+@[-0-9a-z_^\.]+\.[a-z]{2,6}$|i", $email))
			{
				return $email;
			}else{
				return false;
			}
        }else{
			return false;
		}
	}


	/**
	 * Проверка требований пароля
	 * @param	string	$password				Строка для проверки
	 * @return	boolean|string
	 */
	public static function is_valid_password($password, $min_length = 7, $max_length = 25)
	{
		if(!empty($password))
		{
			if(mb_strlen($password) < $min_length || mb_strlen($password) > $max_length)
			{
				return false;
			}

//			if(preg_match("/^[ЁА-яA-z0-9]+$/", $password) === false)
//			{
//				return false;
//			}
//
//			if(preg_match("/[ЁА-ЯA-Z]+/", $password) === false)
//			{
//				//$error = "Пароль должен содержать хотябы 1 заглавную букву";
//				return false;
//			}

			if(!preg_match("/^(?=.*[ёа-яa-z])(?=.*[ЁА-ЯA-Z])(?=.*\d)[a-zA-Z\d]{".$min_length.",".$max_length."}$/", $password))
			{
				//$error = "Пароль должен содержать хотябы 1 заглавную букву";
				return false;
			}

			return true;
        }else{
			return false;
		}
	}


	/**
	 * Проверка телефона
	 * @param	string	$phone					Строка для проверки
	 * @return	string|boolean
	 */
	public static function is_phone($phone)
	{
		$phone = self::clear_phone($phone);

		if(!empty($phone))
		{
			if(!preg_match("/^[0-9]{10,11}+$/", $phone)) return false;

			return $phone;
        }else{
			return false;
		}
	}


	/**
	 * Очистка номера телефона
	 * @param	string	$phone					Строка для очистки
	 * @return	string
	 */
	public static function clear_phone($phone)
	{
		return str_replace(array('+', ' ', '-', '(', ')'), '', trim($phone));
	}


	/**
	 * Очистка домена
	 * @param	string	$url					Строка для очистки
	 * @return	string
	 */
	public static function clear_domain($url)
	{
		return self::mb_str_replace(array("https:", "http:", "/"), '', $url);
	}


	/**
	 * Замена подстроки UTF-8
	 * @param	array	$search					Массив значений для поиска
	 * @param	array	$replace				Массив значений замены
	 * @param	string	$subject				Строка где ищем
	 * @param	int		$count					Ограничение количества замены
	 * @return	string
	 */
	public static function mb_str_replace($search, $replace, $subject, &$count = 0)
	{
		if(!is_array($subject))
		{
			$searches			= is_array($search) ? array_values($search) : array($search);
			$replacements		= is_array($replace) ? array_values($replace) : array($replace);
			$replacements		= array_pad($replacements, count($searches), '');

			foreach($searches as $key => $search)
			{
				$parts			= mb_split(preg_quote($search), $subject);
				$count			+= count($parts) - 1;
				$subject		= implode($replacements[$key], $parts);
			}
		}else{
			foreach ($subject as $key => $value)
			{
				$subject[$key]	= self::mb_str_replace($search, $replace, $value, $count);
			}
		}

		return $subject;
	}


	/**
	 * Кодирование в кирилицу JSON
	 * @param	array	$source					Массив данных
	 * @return	string
	 */
	public static function json_encode_cyr($source)
	{
		return self::decode_unicode(json_encode($source));
	}


	/**
	 * Кодирование в кирилицу
	 * @param type $string
	 */
	public static function decode_unicode($string)
	{
		$arr_replace_utf = array('\u0410', '\u0430','\u0411','\u0431','\u0412','\u0432',
			'\u0413','\u0433','\u0414','\u0434','\u0415','\u0435','\u0401','\u0451','\u0416',
			'\u0436','\u0417','\u0437','\u0418','\u0438','\u0419','\u0439','\u041a','\u043a',
			'\u041b','\u043b','\u041c','\u043c','\u041d','\u043d','\u041e','\u043e','\u041f',
			'\u043f','\u0420','\u0440','\u0421','\u0441','\u0422','\u0442','\u0423','\u0443',
			'\u0424','\u0444','\u0425','\u0445','\u0426','\u0446','\u0427','\u0447','\u0428',
			'\u0448','\u0429','\u0449','\u042a','\u044a','\u042d','\u044b','\u042c','\u044c',
			'\u042d','\u044d','\u042e','\u044e','\u042f','\u044f');

		$arr_replace_cyr = array('А', 'а', 'Б', 'б', 'В', 'в', 'Г', 'г', 'Д', 'д', 'Е', 'е',
			'Ё', 'ё', 'Ж','ж','З','з','И','и','Й','й','К','к','Л','л','М','м','Н','н','О','о',
			'П','п','Р','р','С','с','Т','т','У','у','Ф','ф','Х','х','Ц','ц','Ч','ч','Ш','ш',
			'Щ','щ','Ъ','ъ','Э','ы','Ь','ь','Э','э','Ю','ю','Я','я');

		return str_replace($arr_replace_utf, $arr_replace_cyr, $string);
	}


	/**
	 * Очистка строки
	 * @param	string|array	$text			Исходная строка или массив для очистки
	 * @return	string|array
	 */
	public static function stripinput($text)
	{
		if(!is_array($text))
		{
			$text			= stripslashes(trim($text));
			//$text			= preg_replace("/&[^#0-9]/", "&amp;", $text)
			$search			= array("\"", "'", "\\", '\"', "\'", "<", ">", "&nbsp;");
			$replace		= array("&quot;", "&#39;", "&#92;", "&quot;", "&#39;", "&lt;", "&gt;", " ");
			$text			= preg_replace("/(&amp;)+(?=\#([0-9]{2,3});)/i", "&", self::mb_str_replace($search, $replace, $text));
		}else{
			foreach($text as $key => $value)
			{
				$text[$key]	= self::stripinput($value);
			}
		}

		return $text;
	}


	/**
	 * Транслит
	 * @param	string	$string					Исходная строка
	 * @return	string
	 */
	public static function translit($string)
	{
		$converter = array(
			'а' => 'a',   'б' => 'b',   'в' => 'v',
			'г' => 'g',   'д' => 'd',   'е' => 'e',
			'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
			'и' => 'i',   'й' => 'y',   'к' => 'k',
			'л' => 'l',   'м' => 'm',   'н' => 'n',
			'о' => 'o',   'п' => 'p',   'р' => 'r',
			'с' => 's',   'т' => 't',   'у' => 'u',
			'ф' => 'f',   'х' => 'h',   'ц' => 'c',
			'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
			'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
			'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

			'А' => 'A',   'Б' => 'B',   'В' => 'V',
			'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
			'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
			'И' => 'I',   'Й' => 'Y',   'К' => 'K',
			'Л' => 'L',   'М' => 'M',   'Н' => 'N',
			'О' => 'O',   'П' => 'P',   'Р' => 'R',
			'С' => 'S',   'Т' => 'T',   'У' => 'U',
			'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
			'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
			'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
			'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
		);

		return strtr($string, $converter);
	}


	/**
	 * Транслит URL
	 * @param	string	$str					Исходная строка
	 * @return	string
	 */
	public static function translit_url($str)
	{
		$str	= self::translit($str);
		$str	= preg_replace('~[^-a-z0-9_]+~u', '-', strtolower($str));

		return trim($str, "-");
	}


	/**
	 * Обратное преобразование
	 * @param	string	$text					Исходная строка
	 * @return	string
	 */
	public static function de_stripinput($text)
	{
		if(is_string($text))
		{
			$text		= stripslashes($text);
			$replace	= array("&", "\"", "'", "\\", '\"', "\'", "<", ">");
			$search		= array("&amp;", "&quot;", "&#39;", "&#92;", "&quot;", "&#39;", "&lt;", "&gt;");
			$text		= self::mb_str_replace($search, $replace, $text);
		}

		return $text;	//nl2rb
    }


	/**
	 * Обрезка HTML сущностей
	 * @param	string	$string					Исходная строка
	 * @return	string
	 */
	public static function strip_htmlentities($string)
	{
		$string = preg_replace('~&#x([0-9a-f]+);~i', 'chr(hexdec("\\1"))', $string);
		$string = preg_replace('~&#([0-9]+);~', 'chr("\\1")', $string);
		$trans_tbl = get_html_translation_table(HTML_ENTITIES);
		$trans_tbl = array_flip($trans_tbl);

		return strtr($string, $trans_tbl);
	}


	/**
	 * Конвертация имени файла
	 * @param	string	$filename				Исходная строка
	 * @return	string
	 */
	public static function stripfilename($filename)
	{
		$filename	= self::translit(strtolower($filename));
		$filename = str_replace(" ", "_", $filename);
		$filename = preg_replace("/[^a-zA-Z0-9_-]/", "", $filename);
		$filename = preg_replace("/^\W/", "", $filename);
		$filename = preg_replace('/([_-])\1+/', '$1', $filename);

		if($filename == "")
		{
			$filename = md5(time());
		}

		return $filename;
	}


	/**
	 * Поиск ссылок в строке
	 * @param	string	$text					исходная строка
	 * @return	boolean|array
	 */
	public static function find_variables($text)
	{
		$matches		= array();
		preg_match_all("/\{{(.+?)\}}/", $text, $matches);

		if($matches && isset($matches[1]) && is_array($matches[1]) && count($matches[1]) > 0)
		{
			return $matches[1];
		}else{
			return false;
		}
	}


	/**
	 * Поиск ссылок в строке
	 * @param	string	$text					исходная строка
	 * @return	boolean|array
	 */
	public static function find_parameters($text)
	{
		$matches		= array();
		preg_match_all("/\{{(.+?)\}}/", $text, $matches);
//		preg_match_all("/\[[(.+?)\]]/", $text, $matches);

		if($matches && isset($matches[1]) && is_array($matches[1]) && count($matches[1]) > 0)
		{
			return $matches[1];
		}else{
			return false;
		}
	}


	/**
	 * Поиск ссылок в строке
	 * @param	string	$text					исходная строка
	 * @return	boolean|array
	 */
	public static function find_links($text)
	{
		$matches		= array();
		preg_match_all('/(http:\/\/|https:\/\/|\/\/)?(www)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w\.-\?\%\&]*)*\/?/i', self::mb_str_replace(' ', '', $text), $matches);

		if($matches && isset($matches[0]) && is_array($matches[0]) && count($matches[0]) > 0)
		{
			return $matches[0];
		}else{
			return false;
		}
	}


	/**
	 * Замена переменных
	 * @param	string	$data					Строка или массив, где заменяем
	 * @return	string
	 */
	public static function replace_action($data, $replace_data)
	{
		if(is_array($data))
		{
			foreach($data as $key => $val)
			{
				$data[$key]						= self::replace_action($val, $replace_data);
			}

			return $data;
		}else{var_export($replace_data); die();
			$data								= self::replace_string($data, $replace_data);

			$parameters_data					= self::find_parameters($data);

			if($parameters_data && count($parameters_data) > 0)
			{
				foreach($parameters_data as $parameter_string)
				{
					$parameter_data				= explode('|', $parameter_string);
					$parameter_method			= 'replace_parameter_'.array_shift($parameter_data);

					if($parameter_data && count($parameter_data) > 0 && method_exists('system_Filter', $parameter_method))
					{
						$data					= self::replace_string($data, array($parameter_string => self::{$parameter_method}($parameter_data)));
					}
				}
			}

			return $data;
		}
	}


	/**
	 * Замена переменных в строке
	 * @param	string	$string					Строка, где заменяем
	 * @return	string
	 */
	public static function replace_string($string, $replace_data): string
    {
		foreach($replace_data as $key => $val)
		{
			$string = SystemFilter::mb_str_replace(array('{{'.$key.'}}', '[['.$key.']]'), $val, $string);
		}

		return $string;
	}


	/**
	 * Округление времени в минутах
	 * @param	int		$time_stamp				UNIX_TIME_STAMP для округдения
	 * @param	int		$step					Шаг округления в минутах
	 * @return	string
	 */
	public static function round_time($time_stamp, $step)
	{
		return (floor(floor($time_stamp / 60) / 60) * 3600 + floor(date("i", $time_stamp) / $step) * $step * 60);
	}


	/**
	 * Значение чисел прописью
	 * @param	int		$value					Количество
	 * @param	string	$name					Существительное
	 * @param	boolean	$need_string			Флаг получения полной строки
	 * @param	boolean	$an_array				Флаг получения массива значений
	 * @return	string|boolean
	 */
	public static function morpher_countable($value, $name, $need_string = false, $an_array = false, $padezh = false)
	{
		if(empty($value)) $value = 0;

		if($padezh)
		{
			$result = morpher_spell($value, $name, $padezh);
		}else{
			$result = morpher_spell($value, $name);
		}

		if($need_string === false)
		{
			$pos_1 = mb_strpos($result, '(');
			$pos_2 = mb_strpos($result, ')');

			$response = mb_substr($result, 0, $pos_1).mb_substr($result, $pos_2 + 1);
		}else{
			if($an_array === false)
			{
				$response = $result;
			}else{
				$res			= explode(" ", $result);
				$res_value		= array_shift($res);
				$res_string		= array_pop($res);

				$response = array('val' => $res_value, 'propis' => implode(' ', $res), 'string' => $res_string);
				//$response = array('val' => $res[0], 'propis' => $res[1], 'string' => $res[2]);
			}
		}
		return $response;
	}


	/**
	 * Человекопонятное значение байт
	 * @param	int		$bytes					Значение байт
	 * @return	string
	 */
	public static function human_bytes($bytes)
	{
		$units		= array('Б', 'Кб', 'Мб', 'Гб', 'Тб');

		$bytes		= max($bytes, 0);
		$pow		= floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow		= min($pow, count($units) - 1);
		$bytes		/= pow(1024, $pow);

		return round($bytes, 2) . ' ' . $units[$pow];
	}


	/**
	 * Человекопонятный телефон
	 * @param	string	$phone					Номер телефона
	 * @return	string
	 */
	public static function human_phone($phone, $format = false)
	{
		$mas			= preg_split('//u',(string)$phone,-1,PREG_SPLIT_NO_EMPTY);

		if($format === false)
		{
			$format		= strlen($phone);
		}

		switch($format)
		{
			case 11:
				return '+'.$mas[0].' ('.$mas[1].$mas[2].$mas[3].') '.$mas[4].$mas[5].$mas[6].'-'.$mas[7].$mas[8].'-'.$mas[9].$mas[10];
			break;

			case 10:
				return '+7 ('.$mas[0].$mas[1].$mas[2].') '.$mas[3].$mas[4].$mas[5].'-'.$mas[6].$mas[7].'-'.$mas[8].$mas[9];
			break;

			case 7:
				return $mas[0].$mas[1].$mas[2].'-'.$mas[3].$mas[4].'-'.$mas[5].$mas[6];
			break;

			case 6:
				return $mas[0].$mas[1].'-'.$mas[2].$mas[3].'-'.$mas[4].$mas[5];
			break;

			default:
				return 'неверный формат';
			break;
		}
	}


	/**
	 * Человекопонятные суммы
	 * @param	int		$money					Сумма
	 * @param	boolean	$int					Флаг приведения к целому
	 * @param	boolean	$rubles					Флаг добавления знака рубля
	 * @return	string
	 */
	public static function human_money($money, $int = false, $rubles = false)
	{
		if(($money == 0 || $money == 0.00) && $int === false) $int = true;

		if($int === false)
		{
			if(empty($money)) $money = 0.00;

			$return =  number_format($money, 2, '.', ' ');
		}else{
			if(empty($money)) $money = 0;

			$return = number_format((float)$money, 0, '', ' ');
		}

		if($rubles === true)
		{
			return $return.' &#8381;';
		}else{
			return $return;
		}
	}


	/**
	 * Разница дат
	 * @param	int		$passed					Значение разницы дат
	 * @param	int		$precision				Точность вывода
	 * @return	string
	 */
	public static function relative_time($passed, $precision = 4, $padezh = false)
	{
		$times = array(
			365*24*60*60    =>  'год',
			30*24*60*60     =>  'месяц',
			7*24*60*60      =>  'неделя',
			24*60*60        =>  'день',
			60*60           =>  'час',
			60              =>  'минута',
			1               =>  'секунда',
		);

		$output				= array();
		$exit				= 0;

		foreach($times as $period => $name)
		{
//			if($exit >= $precision || ($exit > 0 && $period < 60)) break;
			if($exit >= $precision) break;

			$result			= floor($passed / $period);

			if($result > 0)
			{
				if($padezh)
				{
					$output[]	= self::morpher_countable($result, $name, false, false, $padezh);
				}else{
					$output[]	= self::morpher_countable($result, $name);
				}

				$passed		-= $result * $period;

				$exit++;
			}elseif($exit > 0){
				$exit++;
			}
		}

		if(count($output) > 1)
		{
			$last			= array_pop($output);
			$output			= implode(', ', $output);

			return $output.' и '.$last;
		}else{
			if(isset($output[0])) return $output[0];
			return 0;
		}
	}


	public static function request_authorisation_token()
	{
		if(isset($_SERVER['Authorization']))
		{
			$header		= trim($_SERVER["Authorization"]);
		}
		elseif(isset($_SERVER['HTTP_AUTHORIZATION']))
		{
			$header		= trim($_SERVER["HTTP_AUTHORIZATION"]);
		}

		if(!empty($header))
		{
			if(preg_match('/Bearer\s(\S+)/', $header, $matches))
			{
				return $matches[1];
			}
		}

		return false;
	}


	/**
	 * Вывод русской даты		rus_date("l, j F Y")
	 * @param	string	$format					Формат вывода
	 * @param	int		$timestamp				Метка времени
	 * @return	string
	 */
    public static function rus_date($format, $timestamp = false)
	{
		$translation = array(
			"am" => "дп",
			"pm" => "пп",
			"AM" => "ДП",
			"PM" => "ПП",
			"Monday" => "Понедельник",
			"Mon" => "Пн",
			"Tuesday" => "Вторник",
			"Tue" => "Вт",
			"Wednesday" => "Среда",
			"Wed" => "Ср",
			"Thursday" => "Четверг",
			"Thu" => "Чт",
			"Friday" => "Пятница",
			"Fri" => "Пт",
			"Saturday" => "Суббота",
			"Sat" => "Сб",
			"Sunday" => "Воскресенье",
			"Sun" => "Вс",
			"January" => "Января",
			"Jan" => "Янв",
			"February" => "Февраля",
			"Feb" => "Фев",
			"March" => "Марта",
			"Mar" => "Мар",
			"April" => "Апреля",
			"Apr" => "Апр",
			"May" => "Мая",
			"May" => "Мая",
			"June" => "Июня",
			"Jun" => "Июн",
			"July" => "Июля",
			"Jul" => "Июл",
			"August" => "Августа",
			"Aug" => "Авг",
			"September" => "Сентября",
			"Sep" => "Сен",
			"October" => "Октября",
			"Oct" => "Окт",
			"November" => "Ноября",
			"Nov" => "Ноя",
			"December" => "Декабря",
			"Dec" => "Дек",
			"st" => "ое",
			"nd" => "ое",
			"rd" => "е",
			"th" => "ое"
		);

		if($timestamp !== false)
		{
			return strtr(date($format, $timestamp), $translation);
		}else{
			return strtr(date($format), $translation);
		}
    }


	/**
	 * Обрезка строки по словам или кол-ву знаков
	 * @param	string	$text					Исходная строка
	 * @param	int		$length					Длинна строки (знаков)
	 * @return	string
	 */
	public static function crop_string($text, $length = 100, $by_words = false, $clear_tags = true)
	{
		if($clear_tags)
		{
			$text		= strip_tags(trim($text));
//			$text		= system_Filter::strip_get($text);
		}else{
			$text		= trim($text);
		}

		if(mb_strlen($text) > $length)
		{
			$text		= mb_strcut($text, 0, $length);
		}else{
			return $text;
		}

		if($by_words === false)
		{
			return $text;
		}

		$words			= explode(' ', $text);
		array_pop($words);

		return rtrim(implode(' ', $words), "!?,.-").' &hellip;';
	}


	/**
	 * Вычисление границ заданного дня
	 * @param $date
	 * @param $format
	 * @return array
	 */
	public static function day_limits_by_date($date, $format = false)
	{
		$date_data				= explode('.', $date);

		$limits					= array();
		$limits['time_start']	= mktime(0, 0, 0, $date_data[1], $date_data[0], $date_data[2]);
		$limits['time_end']		= mktime(23, 59, 59, $date_data[1], $date_data[0], $date_data[2]);

		return $limits;
	}
}
