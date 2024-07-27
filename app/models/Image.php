<?php

namespace Core;

defined('ROOT') or die ("Direct script access denied");

/**
* Image Class
*/
class Image
{
	/**
	 * Resize an image sdq to specified maximum size.
	 *
	 * @param string $filename Path to the image file
	 * @param integer $max_size Maximum size for the width or height
	 * @return string Path to the resized image file
	 */
	public function resize(string $filename, $max_size = 700):string
	{

		/* Check if the file exists */
		if (!file_exists($filename))
			return $filename;

		/* Get the mime type of the image */
		$type = mime_content_type($filename);
		$angle = 0;

		/* Create an image resource based on the image type */
		switch ($type)
		{
			case 'image/jpeg':
				$image = imagecreatefromjpeg($filename);
				break;
			case 'image/png':
				$image = imagecreatefrompng($filename);
				break;
			case 'image/gif':
				$image = imagecreatefromgif($filename);
				break;
			case 'image/webp':
				$image = imagecreatefromwebp($filename);
				break;

			default:
				return $filename;
				break;
		}

		/* Correct orientation for JPEG images */
		if ($type == 'image/jpeg')
		{
			$exif = @exif_read_data($filename);
			if (!empty($exif['Orientation']))
			{
				switch ($exif['Orientation']) {
					case 3:
						$angle = 180;
						break;
					case 5:
						$angle = -90;
						break;
					case 6:
						$angle = -90;
						break;
					case 7:
						$angle = -90;
						break;
					case 8:
						$angle = 90;
						break;

					default:
						$angle = 0;
						break;
				}
			}
		}

		/* Get the original dimensions of the image */
		$src_w = imagesx($image);
		$src_h = imagesy($image);

		/* Calculate the new dimensions while maintaining the aspect ratio */
		if ($src_w > $src_h)
		{
			if ($src_w < $max_size)
				$max_size = $src_w;

			$dst_w = $max_size;
			$dst_h = ($src_h / $src_w) * $max_size;
		}else{

			if ($src_h < $max_size)
				$max_size = $src_h;

			$dst_h = $max_size;
			$dst_w = ($src_w / $src_h) * $max_size;
		}

		/* Round the dimensions */
		$dst_w = round($dst_w);
		$dst_h = round($dst_h);

		/* Create a new true color image */
		$dst_image = imagecreatetruecolor($dst_w, $dst_h);

		/* Handle transparency for PNG images */
		if ($type == 'image/png')
		{
			imagealphablending($dst_image, false);
			imagesavealpha($dst_image, true);
		}

		/* Resize the image */
		imagecopyresampled($dst_image, $image, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
		imagedestroy($image);

		/* Rotate the image if needed */
		if ($type == 'image/jpeg' && $angle != 0)
			$dst_image = imagerotate($dst_image, $angle, 0);

		/* Save the resized image */
		switch ($type)
		{
			case 'image/jpeg':
				imagejpeg($dst_image, $filename, 90);
				break;
			case 'image/png':
				imagepng($dst_image, $filename, 90);
				break;
			case 'image/gif':
				imagegif($dst_image, $filename);
				break;
			case 'image/webp':
				imagewebp($dst_image, $filename, 90);
				break;

			default:
				return $filename;
				break;
		}

		/* Free memory associated with the resized image */
		imagedestroy($dst_image);
		return $filename;

	}


	/**
	* Crop an image to specified dimensions.
	*
	* @param string $filename Path to the image file.
	* @param integer $max_width Maximum width of the cropped image.
	* @param integer $max_height Maximum height of the cropped image.
	*/
	public function crop(string $filename, $max_width = 700, $max_height = 700)
	{
		/* Check if the file exists */
		if (!file_exists($filename))
			return $filename;

		/* Get the mime type of the image */
		$type = mime_content_type($filename);

		/* Create an image resource based on the image type */
		switch ($type)
		{
			case 'image/jpeg':
				$image = imagecreatefromjpeg($filename);
				$image_func = 'imagecreatefromjpeg';
				break;
			case 'image/png':
				$image = imagecreatefrompng($filename);
				$image_func = 'imagecreatefrompng';
				break;
			case 'image/gif':
				$image = imagecreatefromgif($filename);
				$image_func = 'imagecreatefromgif';
				break;
			case 'image/webp':
				$image = imagecreatefromwebp($filename);
				$image_func = 'imagecreatefromwebp';
				break;

			default:
				return $filename;
				break;
		}

		/* Get the original dimensions of the image */
		$src_w = imagesx($image);
		$src_h = imagesy($image);

		/* Determine the maximum size to resize the image to maintain aspect ratio */
		if ($max_width > $max_height)
		{
			if ($src_w > $src_h)
			{
				$max = $max_width;
			}else
			{
				$max = ($src_h / $src_w) * $max_width;
			}
		}else
		{
			if ($src_w > $src_h)
			{
				$max = ($src_w / $src_h) * $max_height;
			}else
			{
				$max = $max_height;
			}
		}

		/* Resize the image first */
		$this->resize($filename, $max);
		$image = $image_func($filename);

		/* Get the new dimensions of the resized image */
		$src_w = imagesx($image);
		$src_h = imagesy($image);

		/* Calculate the cropping position */
		$src_x = 0;
		$src_y = 0;

		if ($max_width > $max_height)
		{
			$src_y = round(($src_h - $max_height) / 2);
		}else
		{
			$src_x = round(($src_w - $max_width) / 2);
		}

		/* Create a new true color image for the cropped image */
		$dst_image = imagecreatetruecolor($max_width, $max_height);
		
		/* Handle transparency for PNG images */
		if ($type == 'image/png')
		{
			imagealphablending($dst_image, false);
			imagesavealpha($dst_image, true);
		}

		/* Crop the image */
		imagecopyresampled($dst_image, $image, 0, 0, $src_x, $src_y, $max_width, $max_height, $max_width, $max_height);
		imagedestroy($image);

		/* Save the cropped image */
		switch ($type)
		{
			case 'image/jpeg':
				imagejpeg($dst_image,$filename,90);
				break;
			case 'image/png':
				imagepng($dst_image,$filename,90);
				break;
			case 'image/gif':
				imagegif($dst_image,$filename);
				break;
			case 'image/webp':
				imagewebp($dst_image,$filename,90);
				break;
			
			default:
				return $filename;
				break;
		}

		/* Free memory associated with the cropped image */
		imagedestroy($dst_image);
		return $filename;
	}

	/**
	* Generate a thumbnail for an image.
   *
   * @param string $filename Path to the image file.
   * @param int $width Width of the thumbnail.
	* @param int $height Height of the thumbnail.
   * @return string Path to the thumbnail image file.
   */
	public function getThumbnail(string $filename, $width = 700, $height = 700):string
	{

		/* Check if the exists */
		if (file_exists($filename))
		{
			/* Get the file extension */
			$ext = explode(".", $filename);
			$ext = end($ext);

			/* Generatethe path for the thumbnail */
			$dest = preg_replace("/\.$ext$/", "_thumbnail.".$ext, $filename);
			
			/* If the thumbnail already exists, return it's path */
			if (file_exists($dest))
				return $dest;

			/* Create a copy of the image file for the thumbnail */
			copy($filename, $dest);
			
			/* Crop the copied image to create the thumbnail */
			$this->crop($dest, $width, $height);

			/* Return the path to the thumbnail */
			return $dest;
		}

		/* If the file doesn't exist, return the original filename */
		return $filename;
	}

}