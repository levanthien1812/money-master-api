<?php

namespace App\Http\Services;

use App\Http\Helpers\FailedData;
use App\Http\Helpers\StorageHelper;
use App\Http\Helpers\SuccessfulData;
use App\Models\User;
use App\Models\Event;
use Exception;
use Illuminate\Support\Str;

class EventService extends BaseService
{
    public function __construct()
    {
        parent::__construct(Event::class);
    }

    public function create(User $user, array $data): object
    {
        try {
            $eventData = array_merge($data, ['user_id' => $user->id]);

            $image = isset($data['image']) ? $data['image'] : null;
            if ($image) {
                $imageUrl = StorageHelper::store($image, '/public/images/events');
                $eventData = array_merge($eventData, ['image' => $imageUrl]);
            }

            $newEvent = Event::create($eventData);

            return new SuccessfulData('Create event successfully!', ['event' => $newEvent]);
        } catch (Exception $error) {
            return new FailedData($error);
        }
    }

    public function get(User $user, array $inputs): object
    {
        try {
            $month = isset($inputs['month']) ? $inputs['month'] : null;
            $year = isset($inputs['year']) ? $inputs['year'] : null;
            $search = isset($inputs['search']) ? $inputs['search'] : null;

            $events = $user->events();

            if ($month) {
                $events->whereMonth('date_begin', '<=', $month)->whereMonth('date_end', '>=', $month);
            }

            if ($year) {
                $events->whereYear('date_begin', '<=', $year)->whereYear('date_end', '>=', $year);
            }

            if ($search) {
                $events->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('description', 'LIKE', '%' . $search . '%')
                    ->orWhere('location', 'LIKE', '%' . $search . '%');
            }

            $events = $events->get();

            return new SuccessfulData('Get events successfully', ['events' => $events]);
        } catch (Exception $error) {
            return new FailedData('Something went wrong when fetching events!', ['error' => $error]);
        }
    }

    public function update(array $data, int $id): object
    {
        try {

            $event = $this->getById($id);
            if (!$event) {
                return new FailedData('Event not found!');
            }

            $image = isset($data['image']) ? $data['image'] : null;
            if ($image) {
                // DELETE OLD IMAGE
                if ($event->image) {
                    $imagePath = Str::after($event->image, '/storage');
                    StorageHelper::delete($imagePath);
                }

                // STORE AND RETREIVE NEW IMAGE
                $imageUrl = StorageHelper::store($image, '/public/images/categories');
            }

            $data = $image ? array_merge($data, ['image' => $imageUrl]) : $data;

            $event->update($data);

            return new SuccessfulData('Update event successfully!');
        } catch (Exception $error) {
            return new FailedData('Failed to update event!', ['error' => $error]);
        }
    }

    public function deleteWithTransactions(int $id): object
    {
        try {
            $event = $this->getById($id);

            if (!$event) {
                return new FailedData('Event not found!');
            }

            if ($event) {
                app(TransactionServices::class)->deleteByEvent($event->id);
            }

            if ($event->image) {
                $imagePath = Str::after($event->image, '/storage');
                StorageHelper::delete($imagePath);
            }

            $this->model::destroy($id);

            return new SuccessfulData('Delete event successfully!');
        } catch (Exception $error) {
            return new FailedData('Failed to delete event!', ['error' => $error]);
        }
    }

    public function deleteWithoutTransactions(int $id): object
    {
        try {
            $event = $this->getById($id);

            if (!$event) {
                return new FailedData('Event not found!');
            }

            if ($event) {
                app(TransactionServices::class)->removeEvent($event->id);
            }

            if ($event->image) {
                $imagePath = Str::after($event->image, '/storage');
                StorageHelper::delete($imagePath);
            }

            $this->model::destroy($id);

            return new SuccessfulData('Delete event successfully!');
        } catch (Exception $error) {
            return new FailedData('Failed to delete event!', ['error' => $error]);
        }
    }

    public function checkExistsById(int $id): bool
    {
        return Event::where('id', $id)->exists();
    }

    public function getById(int $id): ?Event
    {
        return Event::find($id);
    }
}
