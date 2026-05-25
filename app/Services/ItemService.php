<?php

namespace App\Services;


use App\Enums\Ask;
use App\Enums\Status;
use Carbon\Carbon;
use Exception;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ItemVariation;
use App\Http\Requests\ItemRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\ChangeImageRequest;

class ItemService
{
    public $item;
    protected $itemFilter = [
        'name',
        'slug',
        'item_category_id',
        'price',
        'is_featured',
        'item_type',
        'tax_id',
        'status',
        'order',
        'description',
        'except'
    ];

    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';

            return Item::with('media', 'category', 'tax')->where(function ($query) use ($requests) {
                // Các field dạng số/trạng thái nên dùng match chính xác (kích hoạt index trong DB) không nên dùng LIKE
                $exactMatchFields = ['item_category_id', 'price', 'is_featured', 'item_type', 'tax_id', 'status', 'order'];

                foreach ($requests as $key => $value) {
                    // Bỏ qua nếu ko được phép filter hoặc gía trị rỗng
                    if (!in_array($key, $this->itemFilter) || $value === null || $value === '') {
                        continue;
                    }

                    if ($key == "except") {
                        // Tối ưu nhiều where() != thành dùng 1 query whereNotIn nhẹ hơn hẳn
                        $explodes = array_filter(explode('|', $value));
                        if (!empty($explodes)) {
                            $query->whereNotIn('id', $explodes);
                        }
                    } elseif (in_array($key, $exactMatchFields)) {
                        // Tìm đúng chính xác (=)
                        $query->where($key, $value);
                    } else {
                        // Dành cho các cột dạng chữ cần tìm kiếm chuỗi tương đối (name, slug, description)
                        $query->where($key, 'like', '%' . $value . '%');
                    }
                }
            })->orderBy($orderColumn, $orderType)->$method(
                $methodValue
            );
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(ItemRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $this->item = Item::create($request->validated() + ['slug' => Str::slug($request->name)]);
                if ($request->image) {
                    $this->item->addMedia($request->image)->toMediaCollection('item');
                }
                if ($request->variations) {
                    $this->item->variations()->createMany(json_decode($request->variations, true));
                }
                if (!empty($request->ingredients)) {
                    foreach ($request->ingredients as $ingredient) {
                        $this->item->ingredients()->attach($ingredient['id'], ['quantity_per_unit' => $ingredient['quantity']??0]);
                    }
                }

            });
            return $this->item;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(ItemRequest $request, Item $item): Item
    {
        try {
            DB::transaction(function () use ($request, $item) {
                $item->update($request->validated() + ['slug' => Str::slug($request->name)]);
                if ($request->image) {
                    $item->addMedia($request->image)->toMediaCollection('item');
                }
                if ($request->variations) {
                    $variationIdsArray    = [];
                    $variationDeleteArray = [];
                    $oldVariations        = $item->variations->pluck('id')->toArray();
                    foreach (json_decode($request->variations, true) as $variation) {
                        if (isset($variation['id'])) {
                            $variationIdsArray[] = $variation['id'];
                            ItemVariation::where('id', $variation['id'])->update([
                                'name'             => $variation['name'],
                                'price' => $variation['price'],
                            ]);
                        } else {
                            $item->variations()->create($variation);
                        }
                    }

                    if ($variationIdsArray) {
                        foreach ($oldVariations as $oldVariation) {
                            if (!in_array($oldVariation, $variationIdsArray)) {
                                $variationDeleteArray[] = $oldVariation;
                            }
                        }
                    }

                    if ($variationDeleteArray) {
                        ItemVariation::whereIn('id', $variationDeleteArray)->delete();
                    }
                }
                if (!empty($request->ingredients)) {
                    $syncData = [];
                    foreach ($request->ingredients as $ingredient) {
                        $syncData[$ingredient['id']] = [
                            'quantity_per_unit' => $ingredient['quantity'] ?? 0,
                        ];
                    }
                    $item->ingredients()->sync($syncData);
                }
            });
            return Item::find($item->id);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(Item $item)
    {
        try {
            DB::transaction(function () use ($item) {
                $item->variations()->delete();
                $item->extras()->delete();
                $item->addons()->delete();
                $item->delete();
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(Item $item): Item
    {
        try {
            return $item;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function changeImage(ChangeImageRequest $request, Item $item): Item
    {
        try {
            if ($request->image) {
                $item->clearMediaCollection('item');
                $item->addMedia($request->image)->toMediaCollection('item');
            }
            return $item;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function featuredItems()
    {
        try {
            return Item::where(['is_featured' => Ask::YES, 'status' => Status::ACTIVE])->inRandomOrder()->limit(8)->get();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function mostPopularItems(Request $request)
    {
        try {
            $first_date = $request->first_date ? date('Y-m-d', strtotime($request->first_date)) : date('Y-01-01');
            $last_date  = $request->last_date ? date('Y-m-d', strtotime($request->last_date)) : date('Y-12-31');
            $timeArray = ["06:00", "07:00", "08:00", "09:00", "10:00", "11:00", "12:00", "13:00",
                "14:00", "15:00", "16:00", "17:00", "18:00", "19:00", "20:00", "21:00",
                "22:00", "23:00"];

            $popularItemsArray = [];
            $totalOrdersArray = [];
            $items = Item::select('items.id', 'items.name')
                ->join('order_items', 'order_items.item_id', '=', 'items.id')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('items.status', Status::ACTIVE)
                ->whereBetween('orders.order_datetime', [$first_date, $last_date])
                ->groupBy('items.id', 'items.name')
                ->selectRaw('COUNT(order_items.id) as total_sold')
                ->orderByDesc('total_sold')
                ->limit(10)
                ->get();

            foreach ($items as $item) {
                $orderCounts = [];
                foreach ($timeArray as $time) {
                    $first_time = date('H:i', strtotime($time));
                    $last_time = date('H:i', strtotime($time . ' +59 minutes'));
                    $total_orders = DB::table('orders')
                        ->join('order_items', 'order_items.order_id', '=', 'orders.id')
                        ->where('order_items.item_id', $item->id)
                        ->whereBetween('orders.order_datetime', [$first_date, $last_date])
                        ->whereTime('orders.order_datetime', '>=', Carbon::parse($first_time))
                        ->whereTime('orders.order_datetime', '<=', Carbon::parse($last_time))
                        ->count();

                    $orderCounts[] = $total_orders;
                }

                $popularItemsArray[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'total_sold' => array_sum($orderCounts),
                ];
                $totalOrdersArray[] = array_sum($orderCounts);
            }

            return $popularItemsArray;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function itemReport(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            return Item::withCount('orders')->where(function ($query) use ($requests) {
                if (isset($requests['from_date'])  && isset($requests['to_date'])) {
                    $first_date = date('Y-m-d', strtotime($requests['from_date']));
                    $last_date  = date('Y-m-d', strtotime($requests['to_date']));
                    $query->whereDate('created_at', '>=', $first_date)->whereDate(
                        'created_at',
                        '<=',
                        $last_date
                    );
                }
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->itemFilter)) {
                        if ($key == "except") {
                            $explodes = explode('|', $request);
                            if (count($explodes)) {
                                foreach ($explodes as $explode) {
                                    $query->where('id', '!=', $explode);
                                }
                            }
                        } else {
                            $query->where($key, 'like', '%' . $request . '%');
                        }
                    }
                }
            })->orderBy('orders_count', 'desc')->$method(
                $methodValue
            );
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
