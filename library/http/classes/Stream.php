<?php
namespace pillr\library\http;

use \Psr\Http\Message\StreamInterface as StreamInterface;

/**
 * Describes a data stream.
 *
 * Typically, an instance will wrap a PHP stream; this interface provides
 * a wrapper around the most common operations, including serialization of
 * the entire stream to a string.
 */
class Stream implements StreamInterface
{
    private $file_handler;
    private $is_readable;
    private $is_writable;
    private $is_seekable;

    /**
     * Creates a new instance of a Stream
     *
     * @param string $default Specifies data to open the stream with
     * @param bool $is_readable Read permission for stream
     * @param bool $is_writable Write permission for stream
     */
    function __construct(
      $default = '',
      $is_readable = TRUE,
      $is_writable = TRUE,
      $is_seekable = TRUE
    ) {
      $this->is_readable = $is_readable;
      $this->is_writable = $is_writable;
      $this->is_seekable = $is_seekable;

      $this->file_handler = fopen("php://temp", "r+");
      fwrite($this->file_handler, $default);
      $this->rewind();
    }

    /**
     * Reads all data from the stream into a string, from the beginning to end.
     *
     * This method MUST attempt to seek to the beginning of the stream before
     * reading data and read the stream until the end is reached.
     *
     * Warning: This could attempt to load a large amount of data into memory.
     *
     * This method MUST NOT raise an exception in order to conform with PHP's
     * string casting operations.
     *
     * @see http://php.net/manual/en/language.oop5.magic.php#object.tostring
     * @return string
     */
    public function __toString()
    {
      if (!$is_readable || $this->file_handler == null) {
        return '';
      } else {
        return stream_get_contents($this->file_handler, -1, 0);
      }
    }

    /**
     * Closes the stream and any underlying resources.
     *
     * @return void
     */
    public function close()
    {
      $this->checkDetached();
      fclose($this->detach());
    }

    /**
     * Separates any underlying resources from the stream.
     *
     * After the stream has been detached, the stream is in an unusable state.
     *
     * @return resource|null Underlying PHP stream, if any
     */
    public function detach()
    {
      $temp = $this->file_handler;
      $this->file_handler = null;
      return $temp;
    }

    /**
     * Helper function to check if file handler has been detached already
     *
     * @return void
     */
    private function checkDetached() {
      if (!isset($this->file_handler)) {
        throw new \RuntimeException("Detach was called, stream is unusable.");
      }
    }

    /**
     * Get the size of the stream if known.
     *
     * @return int|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize()
    {
      $this->checkDetached();
      return fstat($this->file_handler)['size'];
    }

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return int Position of the file pointer
     * @throws \RuntimeException on error.
     */
    public function tell()
    {
      $this->checkDetached();
      return ftell($this->file_handler);
    }

    /**
     * Returns TRUE if the stream is at the end of the stream.
     *
     * @return bool
     */
    public function eof()
    {
      $this->checkDetached();
      return feof($this->file_handler);
    }

    /**
     * Returns whether or not the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable()
    {
      return $this->is_seekable;
    }

    /**
     * Seek to a position in the stream.
     *
     * @see http://www.php.net/manual/en/function.fseek.php
     * @param int $offset Stream offset
     * @param int $whence Specifies how the cursor position will be calculated
     *     based on the seek offset. Valid values are identical to the built-in
     *     PHP $whence values for `fseek()`.  SEEK_SET: Set position equal to
     *     offset bytes SEEK_CUR: Set position to current location plus offset
     *     SEEK_END: Set position to end-of-stream plus offset.
     * @throws \RuntimeException on failure.
     */
    public function seek($offset, $whence = SEEK_SET)
    {
      $this->checkDetached();

      if (!$this->is_seekable) {
        throw new \RuntimeException("Stream is not seekable.");
      }

      if (fseek($this->file_handler, $offset, $whence) == -1) {
        throw new \RuntimeException("Stream seek failed.");
      }
    }

    /**
     * Seek to the beginning of the stream.
     *
     * If the stream is not seekable, this method will raise an exception;
     * otherwise, it will perform a seek(0).
     *
     * @see seek()
     * @see http://www.php.net/manual/en/function.fseek.php
     * @throws \RuntimeException on failure.
     */
    public function rewind()
    {
      $this->seek(0);
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable()
    {
      return $this->is_writable;
    }

    /**
     * Write data to the stream.
     *
     * @param string $string The string that is to be written.
     * @return int Returns the number of bytes written to the stream.
     * @throws \RuntimeException on failure.
     */
    public function write($string)
    {
      $this->checkDetached();

      if (!$this->is_writable ||
          !($result = fwrite($this->file_handler, $string))) {
        throw new \RuntimeException("Stream cannot be written to.");
      }
      return $result;
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable()
    {
      return $this->is_readable;
    }

    /**
     * Read data from the stream.
     *
     * @param int $length Read up to $length bytes from the object and return
     *     them. Fewer than $length bytes may be returned if underlying stream
     *     call returns fewer bytes.
     * @return string Returns the data read from the stream, or an empty string
     *     if no bytes are available.
     * @throws \RuntimeException if an error occurs.
     */
    public function read($length)
    {
      $this->checkDetached();

      if (!$this->is_readable ||
          !($result = fread($this->file_handler, $length))) {
        throw new \RuntimeException("Unable to read from stream.");
      }
      return $result;
    }

    /**
     * Returns the remaining contents in a string
     *
     * @return string
     * @throws \RuntimeException if unable to read.
     * @throws \RuntimeException if error occurs while reading.
     */
    public function getContents()
    {
      return $this->read($this->size() - $this->tell());
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @see http://php.net/manual/en/function.stream-get-meta-data.php
     * @param string $key Specific metadata to retrieve.
     * @return array|mixed|null Returns an associative array if no key is
     *     provided. Returns a specific key value if a key is provided and the
     *     value is found, or null if the key is not found.
     */
    public function getMetadata($key = null)
    {
      $this->checkDetached();

      $metadata = stream_get_meta_data($this->file_handler);
      if ($key == null) {
        return $metadata;
      } else {
        if (array_key_exists($key, $metadata)) {
          return $metadata[$key];
        } else {
          return null;
        }
      }
    }
}
