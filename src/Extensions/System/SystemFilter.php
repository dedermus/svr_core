<?php
namespace Svr\Core\Extensions\System;

/**
 * Системный класс обработки и фильтрации входящих данных.
 */
class SystemFilter
{
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
     * Замена переменных
     *
     * @param array|string $data Строка или массив, где заменяем
     * @param string       $replace_data
     *
     * @return string
     */
	public static function replace_action(array|string $data, string $replace_data): string
    {
		if(is_array($data))
		{
			foreach($data as $key => $val)
			{
				$data[$key]						= self::replace_action($val, $replace_data);
			}
            return $data;

        } else {
			$data								= self::replace_string($data, $replace_data);

			$parameters_data					= self::find_parameters($data);

			if($parameters_data && count($parameters_data) > 0)
			{
				foreach($parameters_data as $parameter_string)
				{
					$parameter_data				= explode('|', $parameter_string);
					$parameter_method			= 'replace_parameter_'.array_shift($parameter_data);

					if($parameter_data && count($parameter_data) > 0 && method_exists('SystemFilter', $parameter_method))
					{
						$data					= self::replace_string($data, array($parameter_string => self::{$parameter_method}($parameter_data)));
					}
				}
			}

        }
        return $data;
    }

    /**
     * Замена переменных в строке.
     *
     * @param string $string Строка, где заменяем.
     * @param array $replace_data Данные для замены.
     * @return string
     */
    public static function replace_string(string $string, array $replace_data): string
    {
        foreach ($replace_data as $key => $val) {
            $string = self::mb_str_replace(['{{' . $key . '}}', '[[' . $key . ']]'], $val, $string);
        }

        return $string;
    }
}
