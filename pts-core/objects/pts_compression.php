<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2008 - 2010, Phoronix Media
	Copyright (C) 2008 - 2010, Michael Larabel

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

class pts_compression
{
	public static function compress_to_archive($to_compress, $compress_to)
	{
		$compress_to_file = basename($compress_to);
		$compress_base_dir = dirname($to_compress);
		$compress_base_name = basename($to_compress);

		switch(substr($compress_to_file, strpos($compress_to_file, ".") + 1))
		{
			case "tar":
				$extract_cmd = "tar -cf " . $compress_to . " " . $compress_base_name;
				break;
			case "tar.gz":
				$extract_cmd = "tar -czf " . $compress_to . " " . $compress_base_name;
				break;
			case "tar.bz2":
				$extract_cmd = "tar -cjf " . $compress_to . " " . $compress_base_name;
				break;
			case "zip":
				$extract_cmd = "zip -r " . $compress_to . " " . $compress_base_name;
				break;
			default:
				$extract_cmd = null;
				break;
		}

		if($extract_cmd != null)
		{
			shell_exec("cd " . $compress_base_dir . " && " . $extract_cmd . " 2>&1");
		}
	}
	public static function archive_extract($file)
	{
		$file_name = basename($file);
		$file_path = dirname($file);

		switch(substr($file_name, strpos($file_name, ".") + 1))
		{
			case "tar":
				$extract_cmd = "tar -xf";
				break;
			case "tar.gz":
				$extract_cmd = "tar -zxf";
				break;
			case "tar.bz2":
				$extract_cmd = "tar -jxf";
				break;
			case "zip":
				$extract_cmd = "unzip -o";
				break;
			default:
				$extract_cmd = "";
				break;
		}

		shell_exec("cd " . $file_path . " && " . $extract_cmd . " " . $file_name . " 2>&1");
	}
	public static function zip_archive_extract($zip_file, $extract_to)
	{
		if(!class_exists("ZipArchive") || !is_readable($zip_file))
		{
			return false;
		}

		$zip = new ZipArchive();
		$res = $zip->open($zip_file);

		if($res === true && is_writable($extract_to))
		{
			$zip->extractTo($extract_to);
			$zip->close();
			$success = true;
		}
		else
		{
			$success = false;
		}

		return $success;
	}
	public static function zip_archive_create($zip_file, $add_files)
	{
		if(!class_exists("ZipArchive"))
		{
			return false;
		}

		$zip = new ZipArchive();

		if($zip->open($zip_file, ZIPARCHIVE::CREATE) !== true)
		{
			$success = false;
		}
		else
		{
			foreach(pts_to_array($add_files) as $add_file)
			{
				self::zip_archive_add($zip, $add_file, dirname($add_file));
			}

			$success = true;
		}

		return $success;
	}
	protected static function zip_archive_add(&$zip, $add_file, $base_dir = null)
	{
		if(is_dir($add_file))
		{
			$zip->addEmptyDir(substr($add_file, strlen(pts_add_trailing_slash($base_dir))));

			foreach(pts_glob(pts_add_trailing_slash($add_file) . '*') as $new_file)
			{
				self::zip_archive_add($zip, $new_file, $base_dir);
			}
		}
		else if(is_file($add_file))
		{
			$zip->addFile($add_file, substr($add_file, strlen(pts_add_trailing_slash($base_dir))));
		}
	}
}

?>