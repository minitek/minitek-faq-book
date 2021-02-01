<?php
/**
* @title		Minitek FAQ Book
* @copyright	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license		GNU General Public License version 3 or later.
* @author url	https://www.minitek.gr/
* @developers	Minitek.gr
*/

namespace Joomla\Component\FAQBookPro\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\String\StringHelper;
use Joomla\CMS\Component\ComponentHelper;

jimport('joomla.filesystem.folder');

/**
 * FAQ Book Component Utilities Helper.
 *
 * @since  4.0.0
 */
abstract class UtilitiesHelper
{
	public static function getParams($option)
	{
		$application = Factory::getApplication();

		if ($application->isClient('site'))
		{
		  $params = $application->getParams($option);
		}
		else
		{
		  $params = ComponentHelper::getParams($option);
		}

		return $params;
	}

	public static function getWordLimit($text, $limit, $end_char = '&#8230;')
	{
		if(StringHelper::trim($text) == '')
			return $text;

		// always strip tags for text
		$text = strip_tags($text);
		$find = array(
			"/\r|\n/u",
			"/\t/u",
			"/\s\s+/u"
		);
		$replace = array(
			" ",
			" ",
			" "
		);
		$text = preg_replace($find, $replace, $text);

		preg_match('/\s*(?:\S*\s*){'.(int)$limit.'}/u', $text, $matches);

		if (StringHelper::strlen($matches[0]) == StringHelper::strlen($text))
			$end_char = '';

		return StringHelper::rtrim($matches[0]).$end_char;

	}

	public static function getTimeSince($date)
	{
		$date = strtotime($date);
		$now = Factory::getDate()->format("Y-m-d H:i:s");
		$now = strtotime($now);
		$since = $now - $date;

		$chunks = array(
			array(60 * 60 * 24 * 365 , Text::_('COM_FAQBOOKPRO_YEAR'), Text::_('COM_FAQBOOKPRO_YEARS')),
			array(60 * 60 * 24 * 30 , Text::_('COM_FAQBOOKPRO_MONTH'), Text::_('COM_FAQBOOKPRO_MONTHS')),
			array(60 * 60 * 24 * 7, Text::_('COM_FAQBOOKPRO_WEEK'), Text::_('COM_FAQBOOKPRO_WEEKS')),
			array(60 * 60 * 24 , Text::_('COM_FAQBOOKPRO_DAY'), Text::_('COM_FAQBOOKPRO_DAYS')),
			array(60 * 60 , Text::_('COM_FAQBOOKPRO_HOUR'), Text::_('COM_FAQBOOKPRO_HOURS')),
			array(60 , Text::_('COM_FAQBOOKPRO_MINUTE'), Text::_('COM_FAQBOOKPRO_MINUTES')),
			array(1 , Text::_('COM_FAQBOOKPRO_SECOND'), Text::_('COM_FAQBOOKPRO_SECONDS'))
		);

		for ($i = 0, $j = count($chunks); $i < $j; $i++)
		{
			$seconds = $chunks[$i][0];
			$name_1 = $chunks[$i][1];
			$name_n = $chunks[$i][2];

			if (($count = floor($since / $seconds)) != 0)
			{
				break;
			}
		}

		$print = ($count == 1) ? '1 '.$name_1 : "$count {$name_n}";

		return $print;
	}

	public static function getAuthorisedTopics($action)
	{
		// Brute force method: get all published topic rows for the component and check each one
		// TODO: Modify the way permissions are stored in the db to allow for faster implementation and better scaling
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('t.id AS id, a.name AS asset_name')
			->from('#__minitek_faqbook_topics AS t')
			->join('INNER', '#__assets AS a ON t.asset_id = a.id')
			->where('t.published = 1');
		$db->setQuery($query);
		$allTopics = $db->loadObjectList('id');
		$allowedTopics = array();

		foreach ($allTopics as $topic)
		{
			if (Factory::getUser()->authorise($action, $topic->asset_name))
			{
				$allowedTopics[] = (int) $topic->id;
			}
		}

		return $allowedTopics;
	}

	public static function getCustomState($alias)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('id, title, color')
			->from('#__minitek_faqbook_customstates')
			->where('alias = '.$db->quote($alias))
			->where('state = '.$db->quote(1));
		$db->setQuery($query);
		$customstate = $db->loadObject();

		return $customstate;
	}

	public static function userExists($id)
	{
		$db = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'))
			->from($db->quoteName('#__users'))
			->where($db->quoteName('id') . ' = ' . $db->quote($id) . '');
		$db->setQuery($query);
		$row = $db->loadObject();

		return $row;
	}

	public static function getInitials($name)
	{
		$words = preg_split('/\s+/', $name);
		$initials = '';

		foreach ($words as $key => $word)
		{
			$initials .= mb_substr($word, 0, 1);

			if ($key >= 1)
			{
				break;
			}
		}

		return $initials;
	}

	public static function hsl2rgb($h, $s, $l)
	{
		$r;
		$g;
		$b;

    if ($s == 0)
		{
      $r = $g = $b = $l; // achromatic
    }
		else
		{
      $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
      $p = 2 * $l - $q;
      $r = self::hue2rgb($p, $q, $h + 1/3);
      $g = self::hue2rgb($p, $q, $h);
      $b = self::hue2rgb($p, $q, $h - 1/3);
    }

    return array(round($r * 255), round($g * 255), round($b * 255));
	}

	public static function hue2rgb($p, $q, $t)
	{
		if ($t < 0) $t += 1;
		if ($t > 1) $t -= 1;
		if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
		if ($t < 1/2) return $q;
		if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;

		return $p;
	}

	public static function getHue($tstr)
	{
		$hash = 0;

		for ($i = 0; $i < strlen($tstr); $i++)
		{
		  $hash = self::uniord($tstr[$i]) + (($hash << 5) - $hash);
		}

		return $hash;
	}

	public static function uniord($u)
	{
		$k = mb_convert_encoding($u, 'UCS-2LE', 'UTF-8');
		$k1 = ord(substr($k, 0, 1));
		$k2 = ord(substr($k, 1, 1));

		return $k2 * 256 + $k1;
	}

	public static function name2color($name)
	{
		$hue = self::getHue($name) % 360;
		$color = self::hsl2rgb($hue / 1000, 0.30, 0.75);

		return $color;
	}

	public static function getGravatar($email)
	{
		$md5 = md5($email);
		$url = 'https://secure.gravatar.com/avatar/'.$md5.'.jpg?s=64&d=mm';

		return $url;
	}

	public static function getMimeTypesExtensions($mimes)
	{
		$extensions = array();

		$types = array(
			"application/msword" => "doc, dot",
			"application/vnd.openxmlformats-officedocument.wordprocessingml.document" => "docx",
			"application/pdf" => "pdf",
			"application/vnd.ms-excel" => "xla, xlc, xlm, xls, xlt, xlw",
			"application/vnd.oasis.opendocument.spreadsheet" => "ods",
			"application/vnd.ms-powerpoint" => "pot, pps, ppt",
			"application/json" => "json",
			"application/x-tar" => "tar",
			"application/x-zip-compressed" => "zip",
			"application/vnd.rar" => "rar",
			"application/x-7z-compressed" => "7z",
			"audio/mpeg" => "mp3",
			"audio/x-wav" => "wav",
			"image/bmp" => "bmp",
			"image/gif" => "gif",
			"image/jpeg" => "jpe, jpeg, jpg",
			"image/svg+xml" => "svg",
			"image/tiff" => "tif, tiff",
			"image/png" => "png",
			"text/css" => "css",
			"text/html" => "html",
			"text/xml" => "xml",
			"text/javascript" => "js, mjs",
			"text/plain" => "txt",
			"text/csv" => "csv",
			"video/mpeg" => "mp2, mpa, mpe, mpeg, mpg, mpv2",
			"video/mp4" => "mp4",
			"video/quicktime" => "mov, qt",
			"video/x-msvideo" => "avi",
			"font/ttf" => "ttf",
			"font/otf" => "otf",
			"font/woff" => "woff",
			"font/woff2" => "woff2"
		);

		foreach ($mimes as $mime)
		{
			$extensions[] = $types[$mime];
		}

		return implode(', ', $extensions);
	}

	public static function getMimeTypeIcon($mime)
	{
		switch ($mime)
		{
			case 'application/msword':
			case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
				$icon = 'file-word-o';
				break;
			case 'application/pdf':
				$icon = 'file-pdf-o';
				break;
			case 'application/vnd.ms-excel':
			case 'application/vnd.oasis.opendocument.spreadsheet':
				$icon = 'file-excel-o';
				break;
			case 'application/vnd.ms-powerpoint':
				$icon = 'file-powerpoint-o';
				break;
			case 'application/x-tar':
			case 'application/x-zip-compressed':
			case 'application/vnd.rar':
			case 'application/x-7z-compressed':
				$icon = 'file-archive-o';
				break;
			case 'audio/mpeg':
			case 'audio/x-wav':
				$icon = 'file-audio-o';
				break;
			case 'image/bmp':
			case 'image/gif':
			case 'image/jpeg':
			case 'image/svg+xml':
			case 'image/tiff':
			case 'image/png':
				$icon = 'file-image-o';
				break;
			case 'application/json':
			case 'text/css':
			case 'text/html':
			case 'text/xml':
			case 'text/javascript':
				$icon = 'file-code-o';
				break;
			case 'text/plain':
			case 'text/csv':
				$icon = 'file-text-o';
				break;
			case 'video/mpeg':
			case 'video/mp4':
			case 'video/quicktime':
			case 'video/x-msvideo':
				$icon = 'file-video-o';
				break;
			case 'font/ttf':
			case 'font/otf':
			case 'font/woff':
			case 'font/woff2':
				$icon = 'font';
				break;
			default:
				$icon = 'file-text-o';
				break;
		}

		return $icon;
	}

	public static function uploadFile($file)
	{
		// Result
		$result = array();

		$params = ComponentHelper::getParams('com_faqbookpro');

		// Import filesystem libraries. Perhaps not necessary, but does not hurt.
		jimport('joomla.filesystem.file');

		// Clean up filename to get rid of strange characters like spaces etc.
		$filename = \JFile::makeSafe($file['name']);

		// Validate type
		$filetype = $file['type'];
		$accepted_types = $params->get('accepted_types', array('image/gif','image/jpeg','image/png','application/pdf','application/x-zip-compressed'));

		if (!in_array($filetype, $accepted_types))
		{
			$result['success'] = false;
			$result['code'] = 415;
			$result['error'] = Text::_('COM_FAQBOOKPRO_ERROR_UNSUPPORTED_MEDIA_TYPE');

			return $result;
		}

		// Validate size
		$filesize = $file['size'];
		$max_size = $params->get('max_size', 1) * 1000 * 1000;

		if ($filesize > $max_size)
		{
			$result['success'] = false;
			$result['code'] = 413;
			$result['error'] = Text::_('COM_FAQBOOKPRO_ERROR_EXCEEDED_FILE_SIZE_LIMIT');

			return $result;
		}

		// Set up the source file and destination directory
		$src = $file['tmp_name'];
		$dest_dir = JPATH_ROOT . DS . 'media' . DS . 'com_faqbookpro' . DS . 'attachments';

		// Check if destination folder exists
		if (!\JFolder::exists($dest_dir))
		{
			if (!\JFolder::create($dest_dir))
			{
				$result['success'] = false;
				$result['code'] = 500;
				$result['error'] = Text::_('COM_FAQBOOKPRO_ERROR_COULD_NOT_CREATE_FOLDER');

				return $result;
			}
		}

		// Encode file name
		$server_secret = Factory::getConfig()->get('secret','');
		$signature = $filename.microtime().$server_secret;

		if (function_exists('sha256'))
		{
			$encoded_name = sha256($signature);
		}
		else if (function_exists('sha1'))
		{
			$encoded_name = sha1($signature);
		}
		else
		{
			$encoded_name = md5($signature);
		}

		// Set up the destination of the file
		$dest = $dest_dir . DS . $encoded_name;

		// Upload file
		if (!\JFile::upload($src, $dest))
		{
			$result['success'] = false;
			$result['code'] = 500;
			$result['error'] = Text::_('COM_FAQBOOKPRO_ERROR_COULD_NOT_UPLOAD_FILE');
		}
		else
		{
			$result['success'] = true;
			$result['name'] = $filename;
			$result['encoded_name'] = $encoded_name;
		}

		return $result;
	}

	public static function openFile($attachment)
	{
		// Calculate the ETag
		$ETag_raw =
			$attachment->encoded_name.
			$attachment->type.
			$attachment->name.
			$attachment->created.
			$attachment->created_by;

		if (function_exists('sha1'))
		{
			$ETag = sha1($ETag_raw);
		}
		else
		{
			$ETag = md5($ETag_raw);
		}

		// Do we have an If-None-Match header?
		$inm = '';

		if (function_exists('apache_request_headers'))
		{
			$headers = apache_request_headers();

			if (array_key_exists('If-None-Match', $headers))
				$inm = $headers['If-None-Match'];
		}

		if (empty($inm))
		{
			if (array_key_exists('HTTP-IF-NONE-MATCH', $_SERVER))
				$inm = $_SERVER['HTTP-IF-NONE-MATCH'];
		}

		if ($inm == $ETag)
		{
			while (@ob_end_clean());
			header('HTTP/1.0 304 Not Modified');
			jexit();
		}

		\JLoader::import('joomla.filesystem.folder');
		\JLoader::import('joomla.filesystem.file');

		$filepath = \JPath::clean(JPATH_ROOT.'/media/com_faqbookpro/attachments/'.$attachment->encoded_name);
		$basename = $attachment->name;

		if (!\JFile::exists($filepath))
		{
			header('HTTP/1.0 404 Not Found');
			jexit();
		}

		Factory::getApplication()->set('format', 'raw');

		// Disable error reporting and error display
		if (function_exists('error_reporting'))
		{
			$oldErrorReporting = error_reporting(0);
		}

		if (function_exists('ini_set'))
		{
			@ini_set('display_error', 0);
		}

		// Clear cache
		while (@ob_end_clean());

		// Fix IE bugs
		if (isset($_SERVER['HTTP_USER_AGENT']) && strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE'))
		{
			$header_file = preg_replace('/\./', '%2e', $basename, substr_count($basename, '.') - 1);

			if (ini_get('zlib.output_compression'))
			{
				ini_set('zlib.output_compression', 'Off');
			}
		}
		else
		{
			$header_file = $basename;
		}

		@clearstatcache();

		// Send a Date header
		\JLoader::import('joomla.utilities.date');
		$jDate = new \JDate($attachment->created);
		header('Date: '.$jDate->toRFC822());

		// Send an Etag
		header('Etag: '.$ETag);

		// Send MIME headers
		header("Content-Description: File Transfer");

		if (empty($attachment->type))
		{
			header('Content-Type: application/octet-stream');
		}
		else
		{
			header('Content-Type: '.$attachment->type);
		}

		header("Accept-Ranges: bytes");
		header('Content-Disposition: attachment; filename="'.$header_file.'"');
		header('Content-Transfer-Encoding: binary');

		// Notify of filesize, if this info is available
		$filesize = @filesize($filepath);

		if ($filesize > 0)
			header('Content-Length: '.(int)$filesize);

		// Disable time limits
		if (!ini_get('safe_mode'))
		{
			set_time_limit(0);
		}

		// Use 1M chunks for echoing the data to the browser
		@flush();
		$chunksize = 1024 * 1024; // 1M chunks
		$buffer = '';
		$handle = @fopen($filepath, 'rb');

		if ($handle !== false)
		{
			while (!feof($handle))
			{
				$buffer = fread($handle, $chunksize);
				echo $buffer;
				@ob_flush();
				flush();
			}

			@fclose($handle);
		}
		else
		{
			@readfile($filepath);
			@flush();
		}

		// Exit application
		jexit(0);
	}

	public static function deleteFile($name)
	{
		// Import filesystem libraries. Perhaps not necessary, but does not hurt.
		jimport('joomla.filesystem.file');

		$dest_dir = JPATH_ROOT . DS . 'media' . DS . 'com_faqbookpro' . DS . 'attachments';
		$file_path = $dest_dir .DS . $name;

		if (file_exists($file_path))
		{
			\JFile::delete($file_path);

			return true;
		}

		return false;
	}
}
