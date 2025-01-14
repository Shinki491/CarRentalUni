<?php 
interface IFileIO {
  function save($data);
  function load($assoc = true); 
}
abstract class FileIO implements IFileIO {
  protected $filepath;

  public function __construct($filename) {
    if (!is_readable($filename) || !is_writable($filename)) {
      throw new Exception("Data source $filename is invalid.");
    }
    $this->filepath = realpath($filename);
  }

  public function load($assoc = true) {
    // Placeholder implementation
    return [];
  }
}
class JsonIO extends FileIO {
  public function load($assoc = true) {
    $file_content = file_get_contents($this->filepath);
    return json_decode($file_content, $assoc) ?: [];
  }

  public function save($data) {
    $json_content = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents($this->filepath, $json_content);
  }
}
class SerializeIO extends FileIO {
  public function load($assoc = true) {
    $file_content = file_get_contents($this->filepath);
    return unserialize($file_content) ?: [];
  }

  public function save($data) {
    $serialized_content = serialize($data);
    file_put_contents($this->filepath, $serialized_content);
  }
}

interface IStorage {
  function add($record): string;
  function findById(string $id);
  function findAll(array $params = []);
  function findOne(array $params = []);
  function update(string $id, $record);
  function delete(string $id);

  function findMany(callable $condition);
  function updateMany(callable $condition, callable $updater);
  function deleteMany(callable $condition);
}

class Storage implements IStorage {
  protected $contents;
  protected $io;

  public function __construct(IFileIO $io, $assoc = true) {
    $this->io = $io;
    $this->contents = (array)$this->io->load($assoc);
  }

  public function __destruct() {
    $this->io->save($this->contents);
  }

  public function add($record): string {
    // $id = uniqid();
    // if (is_array($record)) {
    //   $record['id'] = $id;
    // }
    // else if (is_object($record)) {
    //   $record->id = $id;
    // }
    $id = $record['id'];
    $record['id'] = $id + 1;
    $this->contents[$id] = $record;
    return $id;
  }

  public function findById(string $id) {
    return $this->contents[$id] ?? NULL;
  }

  public function findAll(array $params = []) {
    return array_filter($this->contents, function ($item) use ($params) {
      foreach ($params as $key => $value) {
        if (((array)$item)[$key] !== $value) {
          return FALSE;
        }
      }
      return TRUE;
    });
  }

  public function findOne(array $params = []) {
    $found_items = $this->findAll($params);
    $first_index = array_keys($found_items)[0] ?? NULL;
    return $found_items[$first_index] ?? NULL;
  }

  public function update(string $id, $record) {
    $this->contents[$id] = $record;
  }

  public function delete(string $id) {
    unset($this->contents[$id]);
  }

  public function findMany(callable $condition) {
    return array_filter($this->contents, $condition);
  }

  public function updateMany(callable $condition, callable $updater) {
    array_walk($this->contents, function (&$item) use ($condition, $updater) {
      if ($condition($item)) {
        $updater($item);
      }
    });
  }

  public function deleteMany(callable $condition) {
    $this->contents = array_filter($this->contents, function ($item) use ($condition) {
      return !$condition($item);
    });
  }

  public function findCarsByFilter(array $filters, $bookingStorage) {
    $bookings = $bookingStorage->findAll();

    return $this->findMany(function ($car) use ($filters, $bookings) {
        // Transmission filter
        if (isset($filters['transmission']) && $filters['transmission'] !== '') {
            if ($car['transmission'] !== $filters['transmission']) {
                return false;
            }
        }

        // Fuel type filter
        if (isset($filters['fuel_type']) && $filters['fuel_type'] !== '') {
            if ($car['fuel_type'] !== $filters['fuel_type']) {
                return false;
            }
        }

        // Passengers filter
        if (isset($filters['passengers']) && $filters['passengers'] !== '') {
            if ((int)$car['passengers'] < (int)$filters['passengers']) {
                return false;
            }
        }

        // Price range filter
        if (isset($filters['price_min']) && $filters['price_min'] !== '') {
            if ((float)$car['daily_price_huf'] < (float)$filters['price_min']) {
                return false;
            }
        }
        if (isset($filters['price_max']) && $filters['price_max'] !== '') {
            if ((float)$car['daily_price_huf'] > (float)$filters['price_max']) {
                return false;
            }
        }

        // Date and Time Availability Filter
        if (isset($filters['start_date'], $filters['end_date']) &&
    $filters['start_date'] !== '' && $filters['end_date'] !== '') {

    $start_date = strtotime($filters['start_date']);
    $end_date = strtotime($filters['end_date']);

    foreach ($bookings as $booking) {
        if ($booking['car_id'] == $car['id']) {
            $booking_start = strtotime($booking['start_date']);
            $booking_end = strtotime($booking['end_date']);

            // Check if requested date range overlaps with booking date range
            if ($start_date <= $booking_end && $end_date >= $booking_start) {
                return false;
            }
        }
      }
    }


    return true; // Car matches all filters
    });
  }

}
