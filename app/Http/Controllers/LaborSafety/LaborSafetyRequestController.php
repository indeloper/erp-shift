<?php

namespace App\Http\Controllers\LaborSafety;

use App\Http\Requests\ProjectRequest\ProjectStatRequest;
use App\Models\Building\ObjectResponsibleUser;
use App\Models\Company\Company;
use App\Models\Company\CompanyReportTemplate;
use App\Models\LaborSafety\LaborSafetyOrderType;
use App\Models\LaborSafety\LaborSafetyOrderWorker;
use App\Models\LaborSafety\LaborSafetyRequest;
use App\Models\LaborSafety\LaborSafetyRequestOrder;
use App\Models\LaborSafety\LaborSafetyRequestStatus;
use App\Models\LaborSafety\LaborSafetyRequestWorker;
use App\Models\LaborSafety\LaborSafetyWorkerType;
use App\Models\Notification;
use App\Models\Employees\Employee;
use App\Models\Employees\Employees1cPost;
use App\Models\Permission;
use App\Models\Project;
use App\Models\ProjectObject;
use App\Models\User;
use App\Models\UserPermission;
use App\Telegram\TelegramServices;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use morphos\English\NounPluralization;
use morphos\Russian\NounDeclension;
use PhpOffice\PhpWord\ComplexType\ProofState;
use PhpOffice\PhpWord\Element\AbstractContainer;
use PhpOffice\PhpWord\Element\Row;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\SimpleType\NumberFormat;
use PhpOffice\PhpWord\Style\Language;
use function morphos\Russian\inflectName;

class LaborSafetyHtml extends Html
{

    /**
     * Add HTML parts.
     *
     * Note: $stylesheet parameter is removed to avoid PHPMD error for unused parameter
     * Warning: Do not pass user-generated HTML here, as that would allow an attacker to read arbitrary
     * files or perform server-side request forgery by passing local file paths or URLs in <img>.
     *
     * @param \PhpOffice\PhpWord\Element\AbstractContainer $element Where the parts need to be added
     * @param string $html The code to parse
     * @param bool $fullHTML If it's a full HTML, no need to add 'body' tag
     * @param bool $preserveWhiteSpace If false, the whitespaces between nodes will be removed
     * @param array $options:
     *                + IMG_SRC_SEARCH: optional to speed up images loading from remote url when files can be found locally
     *                + IMG_SRC_REPLACE: optional to speed up images loading from remote url when files can be found locally
     */
    public static function addHtml($element, $html, $fullHTML = false, $preserveWhiteSpace = true, $options = null)
    {
        /*
         * @todo parse $stylesheet for default styles.  Should result in an array based on id, class and element,
         * which could be applied when such an element occurs in the parseNode function.
         */
        self::$options = $options;

        // Preprocess: remove all line ends, decode HTML entity,
        // fix ampersand and angle brackets and add body tag for HTML fragments
        $html = str_replace(array("\n", "\r"), '', $html);
        $html = str_replace(array('&lt;', '&gt;', '&amp;', '&quot;'), array('_lt_', '_gt_', '_amp_', '_quot_'), $html);
        $html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
        $html = str_replace('&', '&amp;', $html);
        $html = str_replace(array('_lt_', '_gt_', '_amp_', '_quot_'), array('&lt;', '&gt;', '&amp;', '&quot;'), $html);

        if (false === $fullHTML) {
            $html = '<body>' . $html . '</body>';
        }

        // Load DOM
        if (\PHP_VERSION_ID < 80000) {
            $orignalLibEntityLoader = libxml_disable_entity_loader(true);
        }
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = $preserveWhiteSpace;
        $dom->loadXML($html);
        self::$xpath = new \DOMXPath($dom);
        $node = $dom->getElementsByTagName('body');

        self::parseNode($node->item(0), $element);
        if (\PHP_VERSION_ID < 80000) {
            libxml_disable_entity_loader($orignalLibEntityLoader);
        }
    }

    /**
     * Parse a node and add a corresponding element to the parent element.
     *
     * @param \DOMNode $node node to parse
     * @param \PhpOffice\PhpWord\Element\AbstractContainer $element object to add an element corresponding with the node
     * @param array $styles Array with all styles
     * @param array $data Array to transport data to a next level in the DOM tree, for example level of listitems
     */
    protected static function parseNode($node, $element, $styles = array(), $data = array())
    {
        // Populate styles array
        $styleTypes = array('font', 'paragraph', 'list', 'table', 'row', 'cell');
        foreach ($styleTypes as $styleType) {
            if (!isset($styles[$styleType])) {
                $styles[$styleType] = array();
            }
        }

        // Node mapping table
        $nodes = array(
            // $method        $node   $element    $styles     $data   $argument1      $argument2
            'p' => array('Paragraph', $node, $element, $styles, null, null, null),
            'h1' => array('Heading', null, $element, $styles, null, 'Heading1', null),
            'h2' => array('Heading', null, $element, $styles, null, 'Heading2', null),
            'h3' => array('Heading', null, $element, $styles, null, 'Heading3', null),
            'h4' => array('Heading', null, $element, $styles, null, 'Heading4', null),
            'h5' => array('Heading', null, $element, $styles, null, 'Heading5', null),
            'h6' => array('Heading', null, $element, $styles, null, 'Heading6', null),
            '#text' => array('Text', $node, $element, $styles, null, null, null),
            'strong' => array('Property', null, null, $styles, null, 'bold', true),
            'b' => array('Property', null, null, $styles, null, 'bold', true),
            'em' => array('Property', null, null, $styles, null, 'italic', true),
            'i' => array('Property', null, null, $styles, null, 'italic', true),
            'u' => array('Property', null, null, $styles, null, 'underline', 'single'),
            'sup' => array('Property', null, null, $styles, null, 'superScript', true),
            'sub' => array('Property', null, null, $styles, null, 'subScript', true),
            'span' => array('Span', $node, null, $styles, null, null, null),
            'font' => array('Span', $node, null, $styles, null, null, null),
            'table' => array('Table', $node, $element, $styles, null, null, null),
            'tr' => array('Row', $node, $element, $styles, null, null, null),
            'td' => array('Cell', $node, $element, $styles, null, null, null),
            'th' => array('Cell', $node, $element, $styles, null, null, null),
            'ul' => array('List', $node, $element, $styles, $data, null, null),
            'ol' => array('List', $node, $element, $styles, $data, null, null),
            'li' => array('ListItem', $node, $element, $styles, $data, null, null),
            'img' => array('Image', $node, $element, $styles, null, null, null),
            'br' => array('WordBreak', null, $element, $styles, null, null, null),
            'a' => array('Link', $node, $element, $styles, null, null, null),
            'input' => array('Input', $node, $element, $styles, null, null, null),
            'hr' => array('HorizRule', $node, $element, $styles, null, null, null),
            'pagebreak' => array('PageBreak', null, $element, $styles, null, null, null),
        );

        $newElement = null;
        $keys = array('node', 'element', 'styles', 'data', 'argument1', 'argument2');

        if (isset($nodes[$node->nodeName])) {
            // Execute method based on node mapping table and return $newElement or null
            // Arguments are passed by reference
            $arguments = array();
            $args = array();
            list($method, $args[0], $args[1], $args[2], $args[3], $args[4], $args[5]) = $nodes[$node->nodeName];
            for ($i = 0; $i <= 5; $i++) {
                if ($args[$i] !== null) {
                    $arguments[$keys[$i]] = &$args[$i];
                }
            }
            $method = "parse{$method}";
            $newElement = call_user_func_array(array('self', $method), array_values($arguments));

            // Retrieve back variables from arguments
            foreach ($keys as $key) {
                if (array_key_exists($key, $arguments)) {
                    $$key = $arguments[$key];
                }
            }
        }

        if ($newElement === null) {
            $newElement = $element;
        }

        static::parseChildNodes($node, $newElement, $styles, $data);
    }

    /**
     * Parse child nodes.
     *
     * @param \DOMNode $node
     * @param \PhpOffice\PhpWord\Element\AbstractContainer $element
     * @param array $styles
     * @param array $data
     */
    protected static function parseChildNodes($node, $element, $styles, $data)
    {
        if ('li' != $node->nodeName) {
            $cNodes = $node->childNodes;
            if (!empty($cNodes)) {
                foreach ($cNodes as $cNode) {
                    if ($element instanceof AbstractContainer || $element instanceof Table || $element instanceof Row) {
                        self::parseNode($cNode, $element, $styles, $data);
                    }
                }
            }
        }
    }

    /**
     * Parse page break
     *
     * @param \PhpOffice\PhpWord\Element\AbstractContainer $element
     */
    protected static function parsePageBreak($element)
    {
        $element->addPageBreak();
    }

    /**
     * Parse line break
     *
     * @param \PhpOffice\PhpWord\Element\AbstractContainer $element
     */
    protected static function parseWordBreak($element)
    {
        $element->addTextBreak();
    }

    /**
     * Parse list node
     *
     * @param \DOMNode $node
     * @param \PhpOffice\PhpWord\Element\AbstractContainer $element
     * @param array &$styles
     * @param array &$data
     */
    protected static function parseList($node, $element, &$styles, &$data)
    {
        $isOrderedList = $node->nodeName === 'ol';
        if (isset($data['listdepth'])) {
            $data['listdepth']++;
        } else {
            $data['listdepth'] = 0;
            $styles['list'] = 'listStyle_' . self::$listIndex++;
            $style = $element->getPhpWord()->addNumberingStyle($styles['list'], self::getListStyle($isOrderedList));

            // extract attributes start & type e.g. <ol type="A" start="3">
            $start = 0;
            $type = '';
            foreach ($node->attributes as $attribute) {
                switch ($attribute->name) {
                    case 'start':
                        $start = (int) $attribute->value;
                        break;
                    case 'type':
                        $type = $attribute->value;
                        break;
                }
            }

            $levels = $style->getLevels();
            /** @var \PhpOffice\PhpWord\Style\NumberingLevel */
            $level = $levels[0];
            if ($start > 0) {
                $level->setStart($start);
            }
            $type = $type ? self::mapListType($type) : null;
            if ($type) {
                $level->setFormat($type);
            }
        }
        if ($node->parentNode->nodeName === 'li') {
            return $element->getParent();
        }
    }

    /**
     * @param bool $isOrderedList
     * @return array
     */
    protected static function getListStyle($isOrderedList)
    {
        if ($isOrderedList) {
            return array(
                'type'   => 'multilevel',
                'levels' => array(
                    array('format' => NumberFormat::DECIMAL, 'text' => '%1.', 'alignment' => 'right',  'tabPos' => 720,  'left' => 720,  'hanging' => 180),
                    array('format' => NumberFormat::DECIMAL, 'text' => '%1.%2.', 'alignment' => 'right',  'tabPos' => 1440, 'left' => 1440, 'hanging' => 180),
                    array('format' => NumberFormat::BULLET,  'text' => '— ', 'alignment' => 'right', 'tabPos' => 2160, 'left' => 2160, 'hanging' => 180),
                    array('format' => NumberFormat::DECIMAL, 'text' => '%4.', 'alignment' => 'left',  'tabPos' => 2880, 'left' => 2880, 'hanging' => 360),
                    array('format' => NumberFormat::DECIMAL, 'text' => '%5.', 'alignment' => 'left',  'tabPos' => 3600, 'left' => 3600, 'hanging' => 360),
                    array('format' => NumberFormat::DECIMAL, 'text' => '%6.', 'alignment' => 'right', 'tabPos' => 4320, 'left' => 4320, 'hanging' => 180),
                    array('format' => NumberFormat::DECIMAL, 'text' => '%7.', 'alignment' => 'left',  'tabPos' => 5040, 'left' => 5040, 'hanging' => 360),
                    array('format' => NumberFormat::LOWER_LETTER, 'text' => '%8.', 'alignment' => 'left',  'tabPos' => 5760, 'left' => 5760, 'hanging' => 360),
                    array('format' => NumberFormat::LOWER_ROMAN,  'text' => '%9.', 'alignment' => 'right', 'tabPos' => 6480, 'left' => 6480, 'hanging' => 180),
                ),
            );
        }

        return array(
            'type'   => 'hybridMultilevel',
            'levels' => array(
                array('format' => NumberFormat::BULLET, 'text' => '', 'alignment' => 'left', 'tabPos' => 720,  'left' => 720,  'hanging' => 360, 'font' => 'Symbol',      'hint' => 'default'),
                array('format' => NumberFormat::BULLET, 'text' => 'o',  'alignment' => 'left', 'tabPos' => 1440, 'left' => 1440, 'hanging' => 360, 'font' => 'Courier New', 'hint' => 'default'),
                array('format' => NumberFormat::BULLET, 'text' => '', 'alignment' => 'left', 'tabPos' => 2160, 'left' => 2160, 'hanging' => 360, 'font' => 'Wingdings',   'hint' => 'default'),
                array('format' => NumberFormat::BULLET, 'text' => '', 'alignment' => 'left', 'tabPos' => 2880, 'left' => 2880, 'hanging' => 360, 'font' => 'Symbol',      'hint' => 'default'),
                array('format' => NumberFormat::BULLET, 'text' => 'o',  'alignment' => 'left', 'tabPos' => 3600, 'left' => 3600, 'hanging' => 360, 'font' => 'Courier New', 'hint' => 'default'),
                array('format' => NumberFormat::BULLET, 'text' => '', 'alignment' => 'left', 'tabPos' => 4320, 'left' => 4320, 'hanging' => 360, 'font' => 'Wingdings',   'hint' => 'default'),
                array('format' => NumberFormat::BULLET, 'text' => '', 'alignment' => 'left', 'tabPos' => 5040, 'left' => 5040, 'hanging' => 360, 'font' => 'Symbol',      'hint' => 'default'),
                array('format' => NumberFormat::BULLET, 'text' => 'o',  'alignment' => 'left', 'tabPos' => 5760, 'left' => 5760, 'hanging' => 360, 'font' => 'Courier New', 'hint' => 'default'),
                array('format' => NumberFormat::BULLET, 'text' => '', 'alignment' => 'left', 'tabPos' => 6480, 'left' => 6480, 'hanging' => 360, 'font' => 'Wingdings',   'hint' => 'default'),
            ),
        );
    }

    /**
     * Parse list item node
     *
     * @param \DOMNode $node
     * @param \PhpOffice\PhpWord\Element\AbstractContainer $element
     * @param array &$styles
     * @param array $data
     */
    protected static function parseListItem($node, $element, &$styles, $data)
    {
        $styles['paragraph'] = ['align' => 'both'];

        $cNodes = $node->childNodes;
        if (!empty($cNodes)) {
            $listRun = $element->addListItemRun($data['listdepth'], $styles['list'], $styles['paragraph']);
            foreach ($cNodes as $cNode) {
                self::parseNode($cNode, $listRun, $styles, $data);
            }
        }
    }
}

class LaborSafetyRequestController extends Controller
{
    const PAGE_BREAK_DELIMITER = '<pagebreak></pagebreak>'; // Needs to modify vendor component https://github.com/PHPOffice/PHPWord/issues/1601

    /**
     * Display a view of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response\Illuminate\View\View
     */
    public function index()
    {
        return view('labor-safety.labor-safety-orders-and-requests');
    }

    /**
     * Returns the JSON of data.
     *
     * @return string
     */
    public function list(Request $request)
    {
        $loadOptions = json_decode($request['loadOptions']);

        $query = (new LaborSafetyRequest())
            ->dxLoadOptions($loadOptions);

        if (!Auth::user()->can('labor_safety_order_list_access') && !Auth::user()->is_su) {
            $query->where('author_user_id', '=', Auth::id());
        }

        $totalCount = $query->count();

        $jsonData = $query
            ->get(
                [
                    'id',
                    'order_number',
                    'order_date',
                    'company_id',
                    'project_object_id',
                    'author_user_id',
                    'implementer_user_id',
                    'responsible_employee_id',
                    'sub_responsible_employee_id',
                    'request_status_id',
                    DB::raw("IF(ISNULL(`generated_html`) OR `generated_html` = '', 0, 1) as `is_orders_generated`"),
                    'comment'
                ]
            )
            ->toArray();

        return json_encode([
            "data" => $jsonData,
            "totalCount" => $totalCount
        ], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = json_decode($request->all()["data"], JSON_OBJECT_AS_ARRAY);

        $data["author_user_id"] = Auth::id();
        $data["request_status_id"] = 1;

        if (isset($data["workers"])) {
            $workers = $data["workers"];
            unset($data["workers"]);
        }

        DB::beginTransaction();

        $laborSafetyRequestRow = new LaborSafetyRequest($data);

        $laborSafetyRequestRow->save();

        if (isset($workers)) {
            if (isset($laborSafetyRequestRow->responsible_employee_id)) {
                $newWorker = new LaborSafetyRequestWorker(
                    [
                        'request_id' => $laborSafetyRequestRow->id,
                        'worker_employee_id' => $laborSafetyRequestRow->responsible_employee_id,
                        'worker_type_id' => 1
                    ]
                );
                $newWorker->save();
            }

            if (isset($laborSafetyRequestRow->sub_responsible_employee_id)) {
                $newWorker = new LaborSafetyRequestWorker(
                    [
                        'request_id' => $laborSafetyRequestRow->id,
                        'worker_employee_id' => $laborSafetyRequestRow->sub_responsible_employee_id,
                        'worker_type_id' => 2
                    ]
                );
                $newWorker->save();
            }

            if (isset($laborSafetyRequestRow->project_manager_employee_id)) {
                $newWorker = new LaborSafetyRequestWorker(
                    [
                        'request_id' => $laborSafetyRequestRow->id,
                        'worker_employee_id' => $laborSafetyRequestRow->project_manager_employee_id,
                        'worker_type_id' => 9
                    ]
                );
                $newWorker->save();
            }

            if (isset($laborSafetyRequestRow->sub_project_manager_employee_id)) {
                $newWorker = new LaborSafetyRequestWorker(
                    [
                        'request_id' => $laborSafetyRequestRow->id,
                        'worker_employee_id' => $laborSafetyRequestRow->sub_project_manager_employee_id,
                        'worker_type_id' => 10
                    ]
                );
                $newWorker->save();
            }

            foreach ($workers as $worker) {
                $newWorker = new LaborSafetyRequestWorker(
                    [
                        'request_id' => $laborSafetyRequestRow->id,
                        'worker_employee_id' => $worker["worker_employee_id"],
                        'worker_type_id' => $worker["employee_role_id"]
                    ]
                );
                $newWorker->save();

                if (isset($worker['orders'])) {
                    foreach ($worker['orders'] as $orderType) {
                        $orderWorker = new LaborSafetyOrderWorker([
                            'request_id' => $laborSafetyRequestRow->id,
                            'order_type_id' => $orderType,
                            'requests_worker_id' => $newWorker->id
                        ]);
                        $orderWorker->save();
                    }
                }
            }
        }

        DB::commit();

        $this->sendRequestNotification($laborSafetyRequestRow);

        return response()->json([
            'result' => 'ok',
            'key' => $laborSafetyRequestRow->id
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $id = $request->all()["key"];

        $modifiedData = json_decode($request->all()["modifiedData"], JSON_OBJECT_AS_ARRAY);
        $workers = $modifiedData["workers"];
        $editAction = $modifiedData["editAction"];

        unset($modifiedData["workers"]);
        unset($modifiedData["perform_orders"]);
        unset($modifiedData["editAction"]);

        $requestRow = LaborSafetyRequest::findOrFail($id);

        DB::beginTransaction();

        LaborSafetyOrderWorker::where('request_id', '=', $id)->forceDelete();
        LaborSafetyRequestWorker::where('request_id', '=', $id)->forceDelete();

        if (isset($workers)) {
            foreach ($workers as $worker) {
                $requestWorker = new LaborSafetyRequestWorker(
                    [
                        'request_id' => $id,
                        'worker_employee_id' => $worker['worker_employee_id'],
                        'worker_type_id' => $worker['employee_role_id']
                    ]
                );
                $requestWorker->save();

                if (isset($worker['orders'])) {
                    foreach ($worker['orders'] as $orderType) {
                        $orderWorker = new LaborSafetyOrderWorker([
                            'request_id' => $id,
                            'order_type_id' => $orderType,
                            'requests_worker_id' => $requestWorker->id
                        ]);
                        $orderWorker->save();
                    }
                }
            }
        }

        $requestRow->update($modifiedData);

        $generateOrders = Auth::user()->can('labor_safety_generate_documents_access');

        if ($generateOrders) {
            $modifiedData["generated_html"] = $this->generateRequestHtmlData($requestRow);
            $modifiedData["request_status_id"] = 2;
            $modifiedData["implementer_user_id"] = Auth::id();
        };

        switch ($editAction) {
            case "cancelRequest":
                $modifiedData["request_status_id"] = 3;
                break;
            case "completeRequest":
                $modifiedData["request_status_id"] = 4;
                break;
        }

        $requestRow->update($modifiedData);

        DB::commit();

        //$this->sendRequestNotification($requestRow);

        return response()->json([
            'result' => 'ok'
        ], 200);
    }

    public function delete(Request $request) {
        $id = $request["key"];

        $request = LaborSafetyRequest::findOrFail($id);

        DB::beginTransaction();

        LaborSafetyOrderWorker::where('request_id', '=', $id)->delete();
        LaborSafetyRequestWorker::where('request_id', '=', $id)->delete();
        $request->delete();

        DB::commit();

        return response()->json([
            'result' => 'ok'
        ], 200);
    }

    public function generateRequestHtmlData($request)
    {
        $orders = LaborSafetyOrderWorker::join('labor_safety_order_types', 'labor_safety_order_workers.order_type_id', '=', 'labor_safety_order_types.id')
            ->where('labor_safety_order_workers.request_id', '=', $request->id)
            ->distinct()
            ->orderBy('labor_safety_order_workers.order_type_id')
            ->get([
                'labor_safety_order_workers.order_type_id',
                'labor_safety_order_types.order_type_category_id',
                'labor_safety_order_types.name',
                'labor_safety_order_types.short_name',
                'labor_safety_order_types.full_name',
                'labor_safety_order_types.template'
            ]);

        $resultHtml = '';

        foreach ($orders as $order) {
            $orderTemplate = $this->fillTemplateData($request, $order, $order->template);

            $resultHtml .= $this->getCompanyHeaderTemplateWithData($request) . $orderTemplate . self::PAGE_BREAK_DELIMITER;
        }

        $coverLetter = LaborSafetyOrderType::find(26);
        $orderTemplate = $this->fillTemplateData($request, $coverLetter, $coverLetter->template);
        $resultHtml .= $this->getCompanyHeaderTemplateWithData($request) . $orderTemplate;

        return $resultHtml;
    }

    public function getResponsibleEmployeeForOrder($request, $order, $isSubresponsible, $isForeman = false) {
        switch ($order->order_type_id) {
            case 5:
                $workerTypes = !$isSubresponsible ? [1] : [2];
                break;
            case 27:
                $workerTypes = !$isSubresponsible ? ($isForeman ? [1] : [9]) : ($isForeman ? [2] : [10]);
                break;
            default:
                $workerTypesQuery = LaborSafetyOrderWorker::leftJoin('labor_safety_request_workers', 'labor_safety_order_workers.requests_worker_id', '=', 'labor_safety_request_workers.id')
                    ->leftjoin('labor_safety_worker_types', 'labor_safety_request_workers.worker_type_id', '=', 'labor_safety_worker_types.id')
                    ->whereIn('labor_safety_request_workers.worker_type_id', [1, 2, 9, 10])
                    ->where('labor_safety_order_workers.request_id', '=', $request->id)
                    ->where('labor_safety_order_workers.order_type_id', '=', $order->order_type_id)
                    ->orderBy('labor_safety_worker_types.sort_order');

                if ($isSubresponsible) {
                    $workerTypesQuery = $workerTypesQuery->skip(1)->take(1);
                }

                $workerTypes = $workerTypesQuery->pluck('labor_safety_request_workers.worker_type_id')->toArray();
        }

        $orderWorker = LaborSafetyOrderWorker::leftJoin('labor_safety_request_workers', 'labor_safety_order_workers.requests_worker_id', '=', 'labor_safety_request_workers.id')
            ->leftjoin('labor_safety_worker_types', 'labor_safety_request_workers.worker_type_id', '=', 'labor_safety_worker_types.id')
            ->where('labor_safety_order_workers.request_id', '=', $request->id)
            ->where('labor_safety_order_workers.order_type_id', '=', $order->order_type_id)
            ->whereIn('labor_safety_request_workers.worker_type_id', $workerTypes)
            ->orderBy('labor_safety_worker_types.sort_order')
            ->select(['worker_employee_id'])
            ->first();

        if (isset($orderWorker)) {
            return Employee::find($orderWorker->worker_employee_id);
        }
    }

    /**
     * @throws \Exception
     */
    function fillTemplateData($request, $order, $orderTemplate)
    {
        $variables = $this->getArrayOfTemplateVariables($orderTemplate);
        $projectObject = ProjectObject::find($request->project_object_id);

        $responsibleEmployee = $this->getResponsibleEmployeeForOrder($request, $order, false);

        $subResponsibleEmployee = $this->getResponsibleEmployeeForOrder($request, $order, true);

        $months = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];

        $prettyOrderDate = Carbon::parse($request->order_date)->format('«d»') .
            ' ' .
            $months[Carbon::parse($request->order_date)->format('n') - 1] .
            ' ' .
            Carbon::parse($request->order_date)->format('Y г.');

        $orderTemplate = str_replace('<p>{sign_list}</p>', '{sign_list}', $orderTemplate);

        foreach ($variables as $variable) {
            switch ($variable) {
                case "{request_id}":
                    $orderTemplate = str_replace($variable, $request->order_number, $orderTemplate);
                    break;
                case "{template_short_name}":
                    $orderTemplate = str_replace($variable, $order->short_name, $orderTemplate);
                    break;
                case "{order_date}":
                    $orderTemplate = str_replace($variable, Carbon::parse($request->order_date)->format('d.m.Y'), $orderTemplate);
                    break;
                case "{pretty_order_date}":
                    $orderTemplate = str_replace($variable, $prettyOrderDate, $orderTemplate);
                    break;
                case "{responsible_employee_name_initials_before}":
                    if (isset($responsibleEmployee)) {
                        $responsibleEmployeeName = $responsibleEmployee->format('f. p. L', 'именительный');
                        $orderTemplate = str_replace($variable, $responsibleEmployeeName, $orderTemplate);
                    } else {
                        $orderTemplate = str_replace($variable, '<div style="color: #eb975c">[Ответственный не указан]</div>', $orderTemplate);
                    }
                    break;
                case "{responsible_employee_name_initials_after}":
                    if (isset($responsibleEmployee)) {
                        $responsibleEmployeeName = $responsibleEmployee->format('L f. p.', 'винительный');
                        $orderTemplate = str_replace($variable, $responsibleEmployeeName, $orderTemplate);
                    }
                    else {
                        $orderTemplate = str_replace($variable, '<div style="color: #eb975c">[Ответственный не указан]</div>', $orderTemplate);
                    }
                    break;
                case "{responsible_employee_full_name}":
                    if (isset($responsibleEmployee)) {
                        $responsibleEmployeeName = $responsibleEmployee->format('L F P', 'винительный');
                        $orderTemplate = str_replace($variable, $responsibleEmployeeName, $orderTemplate);
                    }
                    else {
                        $orderTemplate = str_replace($variable, '<div style="color: #eb975c">[Ответственный не указан]</div>', $orderTemplate);
                    }
                    break;
                case "{responsible_employee_post}":
                    if (isset($responsibleEmployee)) {
                        $employeePostName = $this->mb_lcfirst(Employees1cPost::find($responsibleEmployee->employee_1c_post_id)->getInflection('винительный'));
                        $orderTemplate = str_replace($variable, $employeePostName, $orderTemplate);
                    } else {
                        $orderTemplate = str_replace($variable, '', $orderTemplate);
                    }
                    break;
                case "{subresponsible_employee_name_initials_after}":
                    if (isset($subResponsibleEmployee)) {
                        $subResponsibleEmployeeName = $subResponsibleEmployee->format('L f. p.', 'винительный');
                        $orderTemplate = str_replace($variable, $subResponsibleEmployeeName, $orderTemplate);
                    } else {
                        $orderTemplate = str_replace($variable, '<div style="color: #eb975c">[Заместитель ответственного не указан]</div>', $orderTemplate);
                    }
                    break;
                case "{subresponsible_employee_name_initials_before}":
                    if (isset($subResponsibleEmployee)) {
                        $subResponsibleEmployeeName = $subResponsibleEmployee->format('f. p. L', 'именительный');
                        $orderTemplate = str_replace($variable, $subResponsibleEmployeeName, $orderTemplate);
                    } else {
                        $orderTemplate = str_replace($variable, '<div style="color: #eb975c">[Заместитель ответственного не указан]</div>', $orderTemplate);
                    }
                    break;
                case "{subresponsible_employee_full_name}":
                    if (isset($subResponsibleEmployee)) {
                        $subResponsibleEmployeeName = $subResponsibleEmployee->format('L F P', 'винительный');
                        $orderTemplate = str_replace($variable, $subResponsibleEmployeeName, $orderTemplate);
                    } else {
                        $orderTemplate = str_replace($variable, '<div style="color: #eb975c">[Заместитель ответственного не указан]</div>', $orderTemplate);
                    }
                    break;
                case "{subresponsible_employee_post}":
                    if (isset($subResponsibleEmployee)) {
                        $employeePostName = $this->mb_lcfirst(Employees1cPost::find($subResponsibleEmployee->employee_1c_post_id)->getInflection('винительный'));
                        $orderTemplate = str_replace($variable, $employeePostName, $orderTemplate);
                    } else {
                        $orderTemplate = str_replace($variable, '', $orderTemplate);
                    }
                    break;
                case "{object_responsible_user_post_name}":
                case "{object_responsible_user_full_name}":
                case "{object_responsible_user_post_name_initials_after}":
                    $objectResponsibleEmployee = $this->getResponsibleEmployeeForOrder($request, $order, false, false);
                    if (isset($objectResponsibleEmployee)) {
                        switch ($variable) {
                            case "{object_responsible_user_post_name}":
                                $objectResponsibleEmployeePostName = $this->mb_lcfirst(Employees1cPost::find($objectResponsibleEmployee->employee_1c_post_id)->getInflection('винительный'));
                                $orderTemplate = str_replace($variable, $objectResponsibleEmployeePostName, $orderTemplate);
                                break;
                            case "{object_responsible_user_full_name}":
                                $objectResponsibleEmployeeName = $objectResponsibleEmployee->format('L F P', 'винительный');
                                $orderTemplate = str_replace($variable, $objectResponsibleEmployeeName, $orderTemplate);
                                break;
                            case "{object_responsible_user_post_name_initials_after}":
                                $objectResponsibleEmployeeName = $objectResponsibleEmployee->format('L f. p.', 'винительный');
                                $orderTemplate = str_replace($variable, $objectResponsibleEmployeeName, $orderTemplate);
                                break;
                        }
                    } else {
                        $orderTemplate = str_replace($variable, '<div style="color: #eb975c">[Заместитель ответственного не указан]</div>', $orderTemplate);
                    }
                    break;
                case "{foreman_user_post_name}":
                case "{foreman_user_full_name}":
                    $foremanEmployee = $this->getResponsibleEmployeeForOrder($request, $order, false, true);
                    if (isset($foremanEmployee)) {
                        switch ($variable) {
                            case "{foreman_user_post_name}":
                                $foremanEmployeePostName = $this->mb_lcfirst(Employees1cPost::find($foremanEmployee->employee_1c_post_id)->getInflection('винительный'));
                                $orderTemplate = str_replace($variable, $foremanEmployeePostName, $orderTemplate);
                                break;
                            case "{foreman_user_full_name}":
                                $foremanEmployeeName = $foremanEmployee->format('L F P', 'винительный');
                                $orderTemplate = str_replace($variable, $foremanEmployeeName, $orderTemplate);
                                break;
                        }
                    } else {
                        $orderTemplate = str_replace('{foreman_user_post_name}', '', $orderTemplate);
                        $orderTemplate = str_replace('{foreman_user_full_name}', '<div style="color: #eb975c">[Ответственный не указан]</div>', $orderTemplate);
                    }
                    break;
                case "{sub_foreman_user_post_name}":
                case "{sub_foreman_user_full_name}":
                    $subForemanEmployee = $this->getResponsibleEmployeeForOrder($request, $order, true, true);
                    if (isset($subForemanEmployee)) {
                        switch ($variable) {
                            case "{sub_foreman_user_post_name}":
                                $subForemanEmployeePostName = $this->mb_lcfirst(Employees1cPost::find($subForemanEmployee->employee_1c_post_id)->getInflection('винительный'));
                                $orderTemplate = str_replace($variable, $subForemanEmployeePostName, $orderTemplate);
                                break;
                            case "{sub_foreman_user_full_name}":
                                $subForemanEmployeeName = $subForemanEmployee->format('L F P', 'винительный');
                                $orderTemplate = str_replace($variable, $subForemanEmployeeName, $orderTemplate);
                                break;
                        }
                    }

                    if (isset($subForemanEmployee) && ($this->isEmployeeParticipatesInOrder($request->id, $subForemanEmployee->id, $order->order_type_id))) {
                        $orderTemplate = str_replace(['[optional-section-start|subresponsible_foreman]', '[optional-section-end|subresponsible_foreman]'], '', $orderTemplate);
                    } else {
                        $pattern = '/\[optional-section-start\|subresponsible_foreman].*?\[optional-section-end\|subresponsible_foreman]/s';
                        $orderTemplate = preg_replace($pattern, '', $orderTemplate);
                    }
                    break;
                case "{project_object_name}":
                    $orderTemplate = str_replace($variable, $projectObject->name, $orderTemplate);
                    break;
                case "{project_object_full_address}":
                    $orderTemplate = str_replace($variable, $projectObject->address, $orderTemplate);
                    break;
                case "{project_object_cadastral_number}":
                    if (!empty($projectObject->cadastral_number)){
                        $orderTemplate = str_replace($variable, ', на земельном участке с кадастровым номером ' . $projectObject->cadastral_number, $orderTemplate);
                    } else {
                        $orderTemplate = str_replace($variable, '', $orderTemplate);
                    }
                    break;
                case "{object_responsible_users}":
                    $objectResponsibleEmployees = $this->getObjectResponsibleEmployees($request, $order);
                    $objectResponsibleEmployeesHtml = '';
                    foreach ($objectResponsibleEmployees as $objectResponsibleEmployee) {
                        $objectResponsibleEmployeePost = Employees1cPost::find($objectResponsibleEmployee->employee_1c_post_id)->getInflection('винительный');
                        $objectResponsibleEmployeesHtml .= ' — ' . $objectResponsibleEmployeePost . ' ' . $objectResponsibleEmployee->format('L F P', 'родительный');
                        $objectResponsibleEmployeesHtml .= '<br/>';
                    }
                    $objectResponsibleEmployeesHtml = trim($objectResponsibleEmployeesHtml, '<br/>');
                    $orderTemplate = str_replace($variable, $objectResponsibleEmployeesHtml, $orderTemplate);
                    break;
                case "{contractor_name}":
                    $contractor = Project::where("object_id", '=', $request->project_object_id)
                        ->leftJoin('contractors', 'projects.contractor_id', '=', 'contractors.id')
                        ->get(['contractors.short_name'])
                        ->first();

                    if (isset($contractor)) {
                        $orderTemplate = str_replace($variable, $contractor->short_name, $orderTemplate);
                    } else {
                        $orderTemplate = str_replace($variable, '<div style="color: #eb975c">Контрагент не указан</div>', $orderTemplate);
                    }
                    break;
                case "{main_engineer_post}":
                    $company = Company::find($request->company_id);
                    $mainEngineerEmployee = Employee::find($company->chief_engineer_employee_id);
                    if (isset($mainEngineerEmployee)) {
                        $mainEngineerEmployeePost = Employees1cPost::find($mainEngineerEmployee->employee_1c_post_id);
                        $orderTemplate = str_replace($variable, $this->mb_lcfirst($mainEngineerEmployeePost->getInflection('винительный')), $orderTemplate);
                    }
                    break;
                case "{main_engineer_full_name}":
                    $company = Company::find($request->company_id);
                    $mainEngineerEmployee = Employee::find($company->chief_engineer_employee_id);
                    if (isset($mainEngineerEmployee)) {
                        $orderTemplate = str_replace($variable, $mainEngineerEmployee->format('L F P', 'винительный'), $orderTemplate);
                    }
                    break;
                case "{main_labor_safety_employee_post}":
                    $laborSafetyRequestWorker = LaborSafetyRequestWorker::where('request_id', '=', $request->id)
                        ->where('worker_type_id', '=', 5)
                        ->first();

                    if (isset($laborSafetyRequestWorker)) {
                        $responsibleMainLaborSafetyEmployee = Employee::find($laborSafetyRequestWorker->worker_employee_id);

                        $responsibleMainLaborSafetyPost = Employees1cPost::find($responsibleMainLaborSafetyEmployee->employee_1c_post_id);

                        $orderTemplate = str_replace($variable, $this->mb_lcfirst($responsibleMainLaborSafetyPost->getInflection('винительный')), $orderTemplate);
                    }
                    break;
                case "{main_labor_safety_employee_full_name}":
                    $laborSafetyRequestWorker = LaborSafetyRequestWorker::where('request_id', '=', $request->id)
                        ->where('worker_type_id', '=', 5)
                        ->first();

                    if (isset($laborSafetyRequestWorker)) {
                        $responsibleMainLaborSafetyEmployee = Employee::find($laborSafetyRequestWorker->worker_employee_id);
                        $orderTemplate = str_replace($variable, $responsibleMainLaborSafetyEmployee->format('L F P', 'винительный'), $orderTemplate);
                    }
                    break;
                case "{main_labor_safety_employee_phone}":
                    $laborSafetyRequestWorker = LaborSafetyRequestWorker::where('request_id', '=', $request->id)
                        ->where('worker_type_id', '=', 5)
                        ->first();

                    if (isset($laborSafetyRequestWorker)) {
                        $responsibleMainLaborSafetyUser = User::find(Employee::find($laborSafetyRequestWorker->worker_employee_id)->user_id);
                        $orderTemplate = str_replace($variable, $responsibleMainLaborSafetyUser->person_phone, $orderTemplate);
                    }
                    break;

                case "{responsible_geodesist_post}":
                    $laborSafetyRequestWorker = LaborSafetyRequestWorker::where('request_id', '=', $request->id)
                        ->where('worker_type_id', '=', 11)
                        ->first();

                    if (isset($laborSafetyRequestWorker)) {
                        $responsibleGeodesistEmployee = Employee::find($laborSafetyRequestWorker->worker_employee_id);

                        $responsibleGeodesistEmployeePost = Employees1cPost::find($responsibleGeodesistEmployee->employee_1c_post_id);

                        $orderTemplate = str_replace($variable, $this->mb_lcfirst($responsibleGeodesistEmployeePost->getInflection('винительный')), $orderTemplate);
                    }
                    break;
                case "{responsible_geodesist_full_name}":
                    $laborSafetyRequestWorker = LaborSafetyRequestWorker::where('request_id', '=', $request->id)
                        ->where('worker_type_id', '=', 11)
                        ->first();

                    if (isset($laborSafetyRequestWorker)) {
                        $responsibleGeodesistEmployee = Employee::find($laborSafetyRequestWorker->worker_employee_id);
                        $orderTemplate = str_replace($variable, $responsibleGeodesistEmployee->format('L F P', 'винительный'), $orderTemplate);
                    }
                    break;
                case "{subresponsible_geodesist_post}":
                case "{subresponsible_geodesist_full_name}":
                    $laborSafetyRequestWorker = LaborSafetyRequestWorker::where('request_id', '=', $request->id)
                        ->where('worker_type_id', '=', 12)
                        ->first();
                    if (isset($laborSafetyRequestWorker)) {
                        $subResponsibleGeodesistEmployee = Employee::find($laborSafetyRequestWorker->worker_employee_id);
                    }

                    if (isset($subResponsibleGeodesistEmployee)) {
                        switch ($variable) {
                            case "{subresponsible_geodesist_post}":
                                $subResponsibleGeodesistEmployeePostName = $this->mb_lcfirst(Employees1cPost::find($subResponsibleGeodesistEmployee->employee_1c_post_id)->getInflection('винительный'));
                                $orderTemplate = str_replace($variable, $subResponsibleGeodesistEmployeePostName, $orderTemplate);
                                break;
                            case "{subresponsible_geodesist_full_name}":
                                $subResponsibleGeodesistEmployeeName = $subResponsibleGeodesistEmployee->format('L F P', 'винительный');
                                $orderTemplate = str_replace($variable, $subResponsibleGeodesistEmployeeName, $orderTemplate);
                                break;
                        }
                    }

                    if (isset($subResponsibleGeodesistEmployee) && ($this->isEmployeeParticipatesInOrder($request->id, $subResponsibleGeodesistEmployee->id, $order->order_type_id))) {
                        $orderTemplate = str_replace(['[optional-section-start|subresponsible_geodesist_employee]', '[optional-section-end|subresponsible_geodesist_employee]'], '', $orderTemplate);
                    } else {
                        $pattern = '/\[optional-section-start\|subresponsible_geodesist_employee].*?\[optional-section-end\|subresponsible_geodesist_employee]/s';
                        $orderTemplate = preg_replace($pattern, '', $orderTemplate);
                    }
                    break;
                case "{responsible_engineer_post}":
                    $laborSafetyRequestWorker = LaborSafetyRequestWorker::where('request_id', '=', $request->id)
                        ->where('worker_type_id', '=', 6)
                        ->first();

                    if (isset($laborSafetyRequestWorker)) {
                        $responsibleEngineerEmployee = Employee::find($laborSafetyRequestWorker->worker_employee_id);

                        $responsibleEngineerEmployeePost = Employees1cPost::find($responsibleEngineerEmployee->employee_1c_post_id);

                        $orderTemplate = str_replace($variable, $this->mb_lcfirst($responsibleEngineerEmployeePost->getInflection('винительный')), $orderTemplate);
                    } else {
                        $orderTemplate = str_replace($variable, '', $orderTemplate);
                    }
                    break;
                case "{responsible_engineer_name}":
                    $laborSafetyRequestWorker = LaborSafetyRequestWorker::where('request_id', '=', $request->id)
                        ->where('worker_type_id', '=', 6)
                        ->first();

                    if (isset($laborSafetyRequestWorker)) {
                        $responsibleEngineerEmployee = Employee::find($laborSafetyRequestWorker->worker_employee_id);
                        $orderTemplate = str_replace($variable, $responsibleEngineerEmployee->format('L F P', 'винительный'), $orderTemplate);
                    } else {
                        $orderTemplate = str_replace($variable, '<div style="color: #eb975c">[Ответственный за исполнительную документацию не указан]</div>', $orderTemplate);
                    }
                    break;
                case "{object_sro_employee_post}":
                    $laborSafetyRequestWorker = LaborSafetyRequestWorker::where('request_id', '=', $request->id)
                        ->where('worker_type_id', '=', 4)
                        ->first();

                    if (isset($laborSafetyRequestWorker)) {
                        $responsibleSROEmployee = Employee::find($laborSafetyRequestWorker->worker_employee_id);

                        $responsibleSROEmployeePost = Employees1cPost::find($responsibleSROEmployee->employee_1c_post_id);

                        $orderTemplate = str_replace($variable, $this->mb_lcfirst($responsibleSROEmployeePost->getInflection('винительный')), $orderTemplate);
                    } else {
                        $orderTemplate = str_replace($variable, '', $orderTemplate);
                    }
                    break;
                case "{object_sro_employee_full_name}":
                    $laborSafetyRequestWorker = LaborSafetyRequestWorker::where('request_id', '=', $request->id)
                        ->where('worker_type_id', '=', 4)
                        ->first();

                    if (isset($laborSafetyRequestWorker)) {
                        $responsibleSROEmployee = Employee::find($laborSafetyRequestWorker->worker_employee_id);
                        $orderTemplate = str_replace($variable, $responsibleSROEmployee->format('L F P', 'винительный'), $orderTemplate);
                    } else {
                        $orderTemplate = str_replace($variable, '<div style="color: #eb975c">[Ответственный за приемку работ не указан]</div>', $orderTemplate);
                    }
                    break;
                case "{object_sro_employee_sro_number}":
                    $orderTemplate = str_replace($variable, '<div style="color: #eb975c">[Номер не указан]</div>', $orderTemplate);
                    break;
                case "{responsible_labor_safety_employee_post}":
                    $laborSafetyRequestWorker = LaborSafetyRequestWorker::where('request_id', '=', $request->id)
                        ->where('worker_type_id', '=', 7)
                        ->first();

                    if (isset($laborSafetyRequestWorker)) {
                        $responsibleLaborSafetyEmployee = Employee::find($laborSafetyRequestWorker->worker_employee_id);

                        $responsibleLaborSafetyEmployeePost = Employees1cPost::find($responsibleLaborSafetyEmployee->employee_1c_post_id);

                        $orderTemplate = str_replace($variable, $this->mb_lcfirst($responsibleLaborSafetyEmployeePost->getInflection('винительный')), $orderTemplate);
                    }
                    break;
                case "{responsible_labor_safety_employee_full_name}":
                    $laborSafetyRequestWorker = LaborSafetyRequestWorker::where('request_id', '=', $request->id)
                        ->where('worker_type_id', '=', 7)
                        ->first();

                    if (isset($laborSafetyRequestWorker)) {
                        $gasWeldingWorksEmployee = Employee::find($laborSafetyRequestWorker->worker_employee_id);
                        $orderTemplate = str_replace($variable, $gasWeldingWorksEmployee->format('L F P', 'винительный'), $orderTemplate);
                    }
                    break;
                case "{gas_welding_works_employee_post}":
                    $laborSafetyRequestWorker = LaborSafetyRequestWorker::where('request_id', '=', $request->id)
                        ->where('worker_type_id', '=', 8)
                        ->first();

                    if (isset($laborSafetyRequestWorker)) {
                        $gasWeldingWorksEmployee = Employee::find(LaborSafetyRequestWorker::where('request_id', '=', $request->id)
                            ->where('worker_type_id', '=', 8)
                            ->first()
                            ->worker_employee_id);

                        $gasWeldingWorksEmployeePost = Employees1cPost::find($gasWeldingWorksEmployee->employee_1c_post_id);

                        $orderTemplate = str_replace($variable, $this->mb_lcfirst($gasWeldingWorksEmployeePost->getInflection('винительный')), $orderTemplate);
                    }
                    break;
                case "{gas_welding_works_employee_full_name}":
                    $gasWeldingWorksEmployee = Employee::find(LaborSafetyRequestWorker::where('request_id', '=', $request->id)
                        ->where('worker_type_id', '=', 8)
                        ->first()
                        ->worker_employee_id);
                    $orderTemplate = str_replace($variable, $gasWeldingWorksEmployee->format('L F P', 'винительный'), $orderTemplate);
                    break;
                case "{gas_welding_works_employee_certificate}":
                    //$orderTemplate = str_replace($variable, $this->getWorkersListForTemplate($request, $order), $orderTemplate);
                    break;
                case "{workers_list}":
                    $workersList = $this->getWorkersListForTemplate($request, $order);
                    if (!empty($workersList)) {
                        $orderTemplate = str_replace($variable, $workersList, $orderTemplate);
                        $orderTemplate = str_replace(['[workers_list_section_start]', '[workers_list_section_end]'], '', $orderTemplate);

                        $pattern = '/\[workers_list_optional_section_start].*?\[workers_list_optional_section_end]/s';
                        $orderTemplate = preg_replace($pattern, '', $orderTemplate);

                        $orderTemplate = str_replace(['[optional-section-start|order_type_10_workers_list]', '[optional-section-end|order_type_10_workers_list]'], '', $orderTemplate);

                        if (isset($subResponsibleEmployee) && $this->isEmployeeParticipatesInOrder($request->id, $subResponsibleEmployee->id, $order->order_type_id)){
                            $orderTemplate = str_replace(['[optional-section-start|subresponsible_employee_and_workers_list]', '[optional-section-end|subresponsible_employee_and_workers_list]'], '', $orderTemplate);
                        } else {
                            $pattern = '/\[optional-section-start\|subresponsible_employee_and_workers_list].*?\[optional-section-end\|subresponsible_employee_and_workers_list]/s';
                            $orderTemplate = preg_replace($pattern, '', $orderTemplate);
                        }
                    } else {
                        $pattern = '/\[workers_list_section_start].*?\[workers_list_section_end]/s';
                        $orderTemplate = preg_replace($pattern, '', $orderTemplate);
                        $orderTemplate = str_replace($variable, $workersList, $orderTemplate);

                        $orderTemplate = str_replace(['[workers_list_optional_section_start]', '[workers_list_optional_section_end]'], '', $orderTemplate);

                        $pattern = '/\[optional-section-start\|order_type_10_workers_list].*?\[optional-section-end\|order_type_10_workers_list]/s';
                        $orderTemplate = preg_replace($pattern, '', $orderTemplate);

                        $pattern = '/\[optional-section-start\|subresponsible_employee_and_workers_list].*?\[optional-section-end\|subresponsible_employee_and_workers_list]/s';
                        $orderTemplate = preg_replace($pattern, '', $orderTemplate);
                    }
                    break;
                case "{sign_list}":
                    $orderTemplate = str_replace($variable, $this->getSignList($request, $order), $orderTemplate);
                    break;
                case "{generated_orders_list}":
                    $orderTemplate = str_replace($variable, $this->getOrdersList($request), $orderTemplate);
                    break;
                case "{company_name}":
                    $company = Company::find($request->company_id);
                    $orderTemplate = str_replace($variable, $company->name, $orderTemplate);
                    break;
            }
        }

        if (isset($subResponsibleEmployee) and ($this->isEmployeeParticipatesInOrder($request->id, $subResponsibleEmployee->id, $order->order_type_id))) {
            $orderTemplate = str_replace(['[optional-section-start|subresponsible_employee]', '[optional-section-end|subresponsible_employee]'], '', $orderTemplate);


        } else {
            $pattern = '/\[optional-section-start\|subresponsible_employee].*?\[optional-section-end\|subresponsible_employee]/s';
            $orderTemplate = preg_replace($pattern, '', $orderTemplate);
        }

        return $orderTemplate;
    }

    function getArrayOfTemplateVariables($template): array
    {
        $variables = [];

        preg_match_all('/\{(.)+?\}/', $template, $variables);
        return array_unique($variables[0]);
    }

    private function mb_lcfirst($string, $charset = 'UTF-8'): string
    {
        return mb_strtolower(mb_substr($string, 0, 1, $charset), $charset) .
            mb_substr($string, 1, mb_strlen($string, $charset), $charset);
    }

    function getObjectResponsibleEmployees($request, $order)
    {
        return Employee::rightJoin('labor_safety_request_workers', 'labor_safety_request_workers.worker_employee_id', '=', 'employees.id')
            ->rightJoin('labor_safety_order_workers', 'labor_safety_order_workers.requests_worker_id', '=', 'labor_safety_request_workers.id')
            ->rightJoin('users', function ($join) {
                $join->on('employees.user_id', '=', 'users.id');
                $join->on('company_id', '=', 'users.company');
            })
            ->where('labor_safety_order_workers.request_id', '=', $request->id)
            ->whereIn('labor_safety_request_workers.worker_type_id', [9, 10])
            ->where('labor_safety_order_workers.order_type_id', '=', $order->order_type_id)
            ->get('employees.*');
    }

    function getWorkersListForTemplate($request, $order)
    {
        $workersList = "";

        $workers = LaborSafetyOrderWorker::where('labor_safety_order_workers.request_id', '=', $request->id)
            ->where('labor_safety_order_workers.order_type_id', '=', $order->order_type_id)
            ->leftJoin('labor_safety_request_workers', 'labor_safety_request_workers.id', '=', 'labor_safety_order_workers.requests_worker_id')
            ->get(
                [
                    'labor_safety_order_workers.*',
                    'labor_safety_request_workers.worker_type_id'
                ]
            );

        foreach ($workers as $worker) {
            switch ($order->order_type_id) {
                case 10: //СВ
                case 11: //Б-ОТ
                case 12: //ПС
                case 27: //В
                    if ($worker->worker_type_id != 3) {
                        continue 2;
                    }
                    break;
                case 20:
                    if ($worker->worker_type_id == 6) {
                        continue 2;
                    }
                    break;
            }

            $employeeId = LaborSafetyRequestWorker::find($worker->requests_worker_id)->worker_employee_id;
            $employee = Employee::find($employeeId);
            $postName = Employees1cPost::find($employee->employee_1c_post_id)->getInflection('винительный');

            $workersList .= '<li>' . $postName . ' — ' . $employee->format('L F P', 'винительный') . ';</li>';
        }


        if (!empty($workersList)) {
            return '<ol style="list-style-type: disc;">' . $workersList . '</ol>';
        } else {
            return null;
        }
    }

    function getSignList($request, $order)
    {
        $signList = '<table style="width: 100%;"><tbody>';

        $workers = LaborSafetyOrderWorker::join('labor_safety_request_workers', 'requests_worker_id', '=', 'labor_safety_request_workers.id')
            ->where('labor_safety_order_workers.request_id', '=', $request->id)
            ->where('order_type_id', '=', $order->order_type_id)
            ->orderBy('worker_type_id')
            ->get(
                [
                    'worker_employee_id'
                ]
            );

        $isWorkerFirstInList = true;

        foreach ($workers as $worker) {
            $employeeName = Employee::find($worker->worker_employee_id)->format(' f. p. L');
            if ($isWorkerFirstInList) {
                $isWorkerFirstInList = false;
                $signList .= '<tr style="height: 76px;"><td style="width: 33%; height: 10px;"><p>С&nbsp;приказом&nbsp;ознакомлен:</p></td><td style="width: 33%; border-bottom: 1px solid black; height: 10px; vertical-align: top;">&nbsp;</td><td style="height: 10px; width: 33%;"><p style="text-align: right;">' . $employeeName . '</p></td></tr><tr style="height: 18px;"><td style="height: 18px; width: 33%;">&nbsp;</td><td style="height: 18px; width: 33%; text-align: center; vertical-align: top;"><span style="font-size: 8pt;">(личная подпись)</span></td><td style="height: 18px; width: 33%;">&nbsp;</td></tr>';
            } else {
                $signList .= '<tr style="height: 76px;"><td style="width: 33%; height: 10px;"></td><td style="width: 33%; border-bottom: 1px solid black; height: 10px; vertical-align: top;">&nbsp;</td><td style="height: 10px; width: 33%;"><p style="text-align: right;">' . $employeeName . '</p></td></tr><tr style="height: 18px;"><td style="height: 18px; width: 33%;">&nbsp;</td><td style="height: 18px; width: 33%; text-align: center; vertical-align: top;"><span style="font-size: 8pt;">(личная подпись)</span></td><td style="height: 18px; width: 33%;">&nbsp;</td></tr>';
            }
        }

        $signList .= '</tbody></table>';

        return $signList;
    }

    function getOrdersList($request)
    {
        $orders = LaborSafetyOrderWorker::where('request_id', '=', $request->id)
            ->leftJoin('labor_safety_order_types', 'labor_safety_order_workers.order_type_id', '=', 'labor_safety_order_types.id')
            ->distinct()
            ->get([
                'labor_safety_order_workers.order_type_id',
                'labor_safety_order_types.full_name',
                'labor_safety_order_types.short_name'
            ]);

        $ordersListHtml = '<ol>';
        foreach ($orders as $order) {
            $ordersListHtml .= '<li>Приказ №' . $request->order_number . '-' . $order->short_name . ' от ' . Carbon::parse($request->order_date)->format('d.m.Y') . ' г. «' . $order->full_name . '» — на 1 листе, 1 экз. — оригинал;</li>';
        }

        $ordersListHtml .= '</ol>';

        return $ordersListHtml;
    }

    function isEmployeeParticipatesInOrder($requestId, $employeeId, $orderTypeId)
    {
        return LaborSafetyOrderWorker::where('worker_employee_id', '=', $employeeId)
            ->where('labor_safety_order_workers.order_type_id', '=', $orderTypeId)
            ->where('labor_safety_order_workers.request_id', '=', $requestId)
            ->leftJoin('labor_safety_request_workers', 'requests_worker_id', '=', 'labor_safety_request_workers.id')
            ->exists();
    }

    function getCompanyHeaderTemplateWithData($request)
    {
        $companyTemplate = CompanyReportTemplate::where('company_id', '=', $request->company_id)
            ->where('template_type', '=', 1)
            ->first()
            ->template;

        $company = Company::find($request->company_id);

        $variables = $this->getArrayOfTemplateVariables($companyTemplate);

        foreach ($variables as $variable) {
            switch ($variable) {
                case "{company_legal_address}":
                    $companyTemplate = str_replace($variable, $company->legal_address, $companyTemplate);
                    break;
                case "{company_phone}":
                    $companyTemplate = str_replace($variable, $company->phone, $companyTemplate);
                    break;
                case "{company_web_site}":
                    $companyTemplate = str_replace($variable, $company->web_site, $companyTemplate);
                    break;
                case "{company_email}":
                    $companyTemplate = str_replace($variable, $company->email, $companyTemplate);
                    break;
                case "{company_name}":
                    $companyTemplate = str_replace($variable, $company->name, $companyTemplate);
                    break;
            }
        }

        return $companyTemplate;
    }

    private function sendRequestNotification($requestRow)
    {
        $notificationText = '';
        $userIds = [];

        switch ($requestRow->request_status_id) {
            case 1:
                $userIds = Permission::getUsersIdsByCodename('labor_safety_generate_documents_access');
                $userIds = array_diff($userIds, array($requestRow->author_user_id));

                $notificationText = (new TelegramServices)->getMessageParams(
                    [
                        'template' => 'laborSafetyNewOrderRequestNotificationTemplate',
                        'orderRequest' => $requestRow
                    ]);
                break;
            case 3:
                $userIds = [$requestRow->author_id];
                $notificationText = "Заявка на формирование приказов #$requestRow->id отменена. Для уточнения информации обратитесь в отдел по Охране Труда.";
                break;
            case 4:
                $userIds = [$requestRow->author_id];
                $notificationText = "Документы по заявке на формирование приказов #$requestRow->id подписаны.";
                break;
        }

        foreach ($userIds as $userId) {
            $notification = new Notification();
            $notification->save();
            $notification->update([
                'name' => $notificationText,
                'target_id' => $requestRow->id,
                'user_id' => $userId,
                'created_at' => now(),
                'type' => 7,
                'status' => 7
            ]);
        }
    }

    public function getRequestWorkers(Request $request)
    {
        $requestId = json_decode($request['requestId']);

        $workers = LaborSafetyRequestWorker::where('request_id', '=', $requestId)
            ->leftJoin('employees', 'labor_safety_request_workers.worker_employee_id', 'employees.id')
            ->leftJoin('companies', 'employees.company_id', '=', 'companies.id')
            ->leftJoin('employees_1c_posts', 'employees.employee_1c_post_id', '=', 'employees_1c_posts.id')
            ->leftJoin('labor_safety_worker_types', 'labor_safety_request_workers.worker_type_id', '=', 'labor_safety_worker_types.id')
            ->orderBy('labor_safety_worker_types.sort_order')
            ->get(
                [
                    'labor_safety_request_workers.id',
                    'labor_safety_request_workers.worker_employee_id',
                    'labor_safety_worker_types.id as employee_role_id',
                    'labor_safety_worker_types.name as employee_role',
                    'employees.id as employee_id',
                    'employee_1c_name',
                    'companies.name as company_name',
                    'employees_1c_posts.name as post_name'
                ]
            )
            ->toArray();

        foreach ($workers as $key => $value) {
            $workers[$key]['orders'] = LaborSafetyOrderWorker::where('request_id', '=', $requestId)
                ->where('requests_worker_id', '=', $value['id'])
                ->pluck('order_type_id');
        }

        return json_encode($workers);
    }

    public function getRequestWorkersTypes(Request $request)
    {
        $options = json_decode($request['data']);

        return (new LaborSafetyWorkerType())
            ->dxLoadOptions($options)
            ->orderBy('sort_order')
            ->get(['id', 'name'])
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function statusesList(Request $request)
    {
        $options = json_decode($request['data']);

        return (new LaborSafetyRequestStatus())
            ->dxLoadOptions($options)
            ->orderBy('id')
            ->get(['id', 'name'])
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    function download(Request $request)
    {
        $requestId = json_decode($request->input('requestId'));
        $request = LaborSafetyRequest::findOrFail($requestId);
        $html = $request->generated_html;

        $html = str_replace('<br>', '<br/>', $html);
        $html = str_replace('<hr>', '<hr/>', $html);

        $phpWord = new PhpWord();

        $title = 'Список приказов №' . $request-> order_number;

        $phpWord->setDefaultParagraphStyle(["spaceBefore" => 0, "spaceAfter" => 0]);
        $phpWord->setDefaultFontName('Calibri');

        $proofState = new ProofState();
        $proofState->setGrammar(ProofState::CLEAN);
        $proofState->setSpelling(ProofState::CLEAN);

        $phpWord->getSettings()->setDecimalSymbol(',');
        $phpWord->getSettings()->setThemeFontLang(new Language(Language::RU_RU));
        $phpWord->getSettings()->setProofState($proofState);

        $phpWord->getDocInfo()->setCreator(Auth::user()->full_name);
        $phpWord->getDocInfo()->setCompany(Company::find($request->company_id)->name);

        $section = $phpWord->addSection();

        $LaborSafetyHtml = new LaborSafetyHtml();
        $LaborSafetyHtml->addHtml($section, $html, false, false);
        $phpWord->save($title. '.docx', 'Word2007', true);
        exit;
    }
}
