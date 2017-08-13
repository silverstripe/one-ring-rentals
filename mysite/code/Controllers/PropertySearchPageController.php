<?php

use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\ORM\ArrayLib;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\Form;

class PropertySearchPageController extends PageController
{
    const NUM_PROPERTIES_PER_PAGE = 15;

    /**
     * Constants representing GET parameters used to filter queries in the class.
     */
    const GET_FILTER_KEYWORDS = 'Keywords';
    const GET_FILTER_ARRIVAL_DATE = 'ArrivalDate';
    const GET_FILTER_NIGHTS = 'Nights';
    const GET_FILTER_BEDROOMS = 'Bedrooms';
    const GET_FILTER_BATHROOMS = 'Bathrooms';
    const GET_FILTER_MIN_PRICE = 'MinPrice';
    const GET_FILTER_MAX_PRICE = 'MaxPrice';

    /**
     * @param HTTPRequest $request
     * @return array | string
     */
    public function index(HTTPRequest $request)
    {
        $paginatedProperties = $this->filterQuery($request);

        $data = array(
            'Results' => $paginatedProperties
        );

        if ($request->isAjax()) {
            return $this->customise($data)
                ->renderWith('Includes/PropertySearchResults');
        }

        return $data;
    }

    /**
     * @return Form
     */
    public function PropertySearchForm()
    {
        $nights = array();
        foreach (range(1, 14) as $i) {
            $nights[$i] = "$i night" . (($i > 1) ? 's' : '');
        }

        $prices = array();
        foreach (range(100, 1000, 50) as $i) {
            $prices[$i] = '$' . $i;
        }

        $form = Form::create(
            $this,
            'PropertySearchForm',
            FieldList::create(
                TextField::create(self::GET_FILTER_KEYWORDS)
                    ->setAttribute('placeholder', 'City, State, Country, etc...')
                    ->addExtraClass('form-control'),
                TextField::create(self::GET_FILTER_ARRIVAL_DATE, 'Arrive on...')
                    ->setAttribute('data-datepicker', true)
                    ->setAttribute('data-date-format', 'DD-MM-YYYY')
                    ->addExtraClass('form-control'),
                DropdownField::create(self::GET_FILTER_NIGHTS, 'Stay for...')
                    ->setSource($nights)
                    ->addExtraClass('form-control'),
                DropdownField::create(self::GET_FILTER_BEDROOMS)
                    ->setSource(ArrayLib::valuekey(range(1, 5)))
                    ->addExtraClass('form-control'),
                DropdownField::create(self::GET_FILTER_BATHROOMS)
                    ->setSource(ArrayLib::valuekey(range(1, 5)))
                    ->addExtraClass('form-control'),
                DropdownField::create(self::GET_FILTER_MIN_PRICE, 'Min. price')
                    ->setEmptyString('-- any --')
                    ->setSource($prices)
                    ->addExtraClass('form-control'),
                DropdownField::create(self::GET_FILTER_MAX_PRICE, 'Max. price')
                    ->setEmptyString('-- any --')
                    ->setSource($prices)
                    ->addExtraClass('form-control')
            ),
            FieldList::create(
                FormAction::create('doPropertySearch', 'Search')
                    ->addExtraClass('btn-lg btn-fullcolor')
            )
        );

        $form->setFormMethod('GET')
            ->setFormAction($this->Link())
            ->disableSecurityToken()
            ->loadDataFrom($this->request->getVars());

        return $form;
    }

    /**
     * @param HTTPRequest $request
     * @return PaginatedList
     */
    protected function filterQuery(HTTPRequest $request)
    {
        $properties = Property::get();
        if ($search = $request->requestVar(self::GET_FILTER_KEYWORDS)) {
            $properties = $properties->filter(array(
                'Title:PartialMatch' => $search
            ));
        }

        if ($arrival = $request->requestVar(self::GET_FILTER_ARRIVAL_DATE)) {
            $arrivalStamp = strtotime($arrival);
            $nightAdder = '+' . (int) $request->requestVar(self::GET_FILTER_NIGHTS) . ' days';
            $startDate = date('Y-m-d', $arrivalStamp);
            $endDate = date('Y-m-d', strtotime($nightAdder, $arrivalStamp));

            $properties = $properties->filter(array(
                'AvailableStart:LessThanOrEqual' => $startDate,
                'AvailableEnd:GreaterThanOrEqual' => $endDate
            ));
        }

        if ($bedrooms = $request->requestVar(self::GET_FILTER_BEDROOMS)) {
            $properties = $properties->filter(array(
                'Bedrooms:GreaterThanOrEqual' => (int) $bedrooms
            ));
        }

        if ($bathrooms = $request->requestVar(self::GET_FILTER_BATHROOMS)) {
            $properties = $properties->filter(array(
                'Bathrooms:GreaterThanOrEqual' => (int) $bathrooms
            ));
        }

        if ($minPrice = $request->requestVar(self::GET_FILTER_MIN_PRICE)) {
            $properties = $properties->filter(array(
                'PricePerNight:GreaterThanOrEqual' => (int) $minPrice
            ));
        }

        if ($maxPrice = $request->requestVar(self::GET_FILTER_MAX_PRICE)) {
            $properties = $properties->filter(array(
                'PricePerNight:LessThanOrEqual' => (int) $maxPrice
            ));
        }

        $paginatedProperties = PaginatedList::create(
            $properties,
            $request
        )->setPageLength(self::NUM_PROPERTIES_PER_PAGE)
            ->setPaginationGetVar('s');

        return $paginatedProperties;
    }
}