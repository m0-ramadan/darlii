<?php

namespace App\Support;

/**
 * بيانات محافظة بني سويف — المرجع الوحيد المستخدم في كل بيانات المنصة.
 *
 * Every city/district carries the real approximate coordinates and an
 * EGP-per-square-metre band used to price listings realistically, plus the
 * street, landmark and compound names that appear in Arabic addresses and
 * descriptions. Arabic + English names live side by side so both locales are
 * always populated.
 *
 * Coordinate sanity: the governorate spans roughly lat 28.55–29.35 N and
 * lng 30.55–31.60 E. Beni Suef city centre is 29.0661 N, 31.0994 E.
 */
class BeniSuefCatalog
{
    public const GOVERNORATE = [
        'ar' => 'بني سويف',
        'en' => 'Beni Suef',
        'lat' => 29.0661,
        'lng' => 31.0994,
    ];

    /**
     * Bounding box of the real Beni Suef Governorate (it stretches from
     * roughly 28.5 N at El Fashn up to 29.5 N at El Wasta, and from the Nile
     * valley edge at 30.4 E to the eastern desert at 31.75 E).
     * Used for validation and for keeping jittered points inside the governorate.
     */
    public const BOUNDS = [
        'lat_min' => 28.50,
        'lat_max' => 29.55,
        'lng_min' => 30.40,
        'lng_max' => 31.75,
    ];

    /**
     * Cities / administrative centres of the governorate.
     *
     * @return list<array{key:string, ar:string, en:string, lat:float, lng:float, price_min:int, price_max:int, rent_min:int, rent_max:int}>
     */
    public static function cities(): array
    {
        return [
            ['key' => 'beni-suef-city', 'ar' => 'مدينة بني سويف', 'en' => 'Beni Suef City', 'lat' => 29.0661, 'lng' => 31.0994, 'price_min' => 14000, 'price_max' => 30000, 'rent_min' => 6000, 'rent_max' => 16000],
            ['key' => 'new-beni-suef', 'ar' => 'مدينة بني سويف الجديدة', 'en' => 'New Beni Suef City', 'lat' => 29.0335, 'lng' => 31.1610, 'price_min' => 13000, 'price_max' => 26000, 'rent_min' => 7000, 'rent_max' => 18000],
            ['key' => 'west-beni-suef', 'ar' => 'بني سويف الغربية', 'en' => 'West Beni Suef', 'lat' => 29.0740, 'lng' => 31.0745, 'price_min' => 12000, 'price_max' => 22000, 'rent_min' => 5000, 'rent_max' => 12000],
            ['key' => 'el-wasta', 'ar' => 'الواسطى', 'en' => 'El Wasta', 'lat' => 29.3390, 'lng' => 31.2110, 'price_min' => 8500, 'price_max' => 15000, 'rent_min' => 3000, 'rent_max' => 7000],
            ['key' => 'nasser', 'ar' => 'ناصر', 'en' => 'Nasser', 'lat' => 29.1810, 'lng' => 31.0630, 'price_min' => 8000, 'price_max' => 14000, 'rent_min' => 3000, 'rent_max' => 6500],
            ['key' => 'fashn', 'ar' => 'الفشن', 'en' => 'El Fashn', 'lat' => 28.7950, 'lng' => 30.8890, 'price_min' => 7500, 'price_max' => 13000, 'rent_min' => 2500, 'rent_max' => 6000],
            ['key' => 'biba', 'ar' => 'ببا', 'en' => 'Biba', 'lat' => 28.9210, 'lng' => 30.9850, 'price_min' => 7500, 'price_max' => 13500, 'rent_min' => 2500, 'rent_max' => 6000],
            ['key' => 'sumusta', 'ar' => 'سمسطا', 'en' => 'Sumusta', 'lat' => 28.9110, 'lng' => 30.8510, 'price_min' => 7000, 'price_max' => 12500, 'rent_min' => 2500, 'rent_max' => 5500],
            ['key' => 'ihnasya', 'ar' => 'إهناسيا', 'en' => 'Ihnasya', 'lat' => 29.0780, 'lng' => 30.9430, 'price_min' => 7000, 'price_max' => 13000, 'rent_min' => 2500, 'rent_max' => 5500],
        ];
    }

    /**
     * Neighbourhoods inside the main city, used for richer addresses.
     * parent = city key.
     *
     * @return list<array{key:string, ar:string, en:string, parent:string, lat:float, lng:float}>
     */
    public static function districts(): array
    {
        return [
            // مدينة ومركز بني سويف
            ['key' => 'university-district', 'ar' => 'حي الجامعة', 'en' => 'University District', 'parent' => 'beni-suef-city', 'lat' => 29.0873, 'lng' => 31.1156],
            ['key' => 'el-hay-el-awal', 'ar' => 'الحي الأول', 'en' => 'First District', 'parent' => 'beni-suef-city', 'lat' => 29.0702, 'lng' => 31.0901],
            ['key' => 'el-hay-el-thani', 'ar' => 'الحي الثاني', 'en' => 'Second District', 'parent' => 'beni-suef-city', 'lat' => 29.0735, 'lng' => 31.0862],
            ['key' => 'bayad-el-arab', 'ar' => 'بياض العرب', 'en' => 'Bayad El Arab', 'parent' => 'beni-suef-city', 'lat' => 29.0592, 'lng' => 31.0851],
            ['key' => 'el-zahra', 'ar' => 'الزهراء', 'en' => 'El Zahraa', 'parent' => 'beni-suef-city', 'lat' => 29.0784, 'lng' => 31.1042],
            ['key' => 'el-masaken', 'ar' => 'المساكن الشعبية', 'en' => 'El Masaken', 'parent' => 'beni-suef-city', 'lat' => 29.0648, 'lng' => 31.0931],
            ['key' => 'el-nile-corniche', 'ar' => 'كورنيش النيل', 'en' => 'Nile Corniche', 'parent' => 'beni-suef-city', 'lat' => 29.0791, 'lng' => 31.0958],
            ['key' => 'el-matafy', 'ar' => 'المطافي', 'en' => 'El Matafy', 'parent' => 'beni-suef-city', 'lat' => 29.0683, 'lng' => 31.1085],
            ['key' => 'el-sail', 'ar' => 'السيل', 'en' => 'El Sail', 'parent' => 'beni-suef-city', 'lat' => 29.0852, 'lng' => 31.1032],
            ['key' => 'ezbet-el-nakhl', 'ar' => 'عزبة النخل', 'en' => 'Ezbet El Nakhl', 'parent' => 'beni-suef-city', 'lat' => 29.0555, 'lng' => 31.0771],
            ['key' => 'el-abasiri', 'ar' => 'الأباصيري', 'en' => 'El Abasiri', 'parent' => 'beni-suef-city', 'lat' => 29.0720, 'lng' => 31.0960],
            ['key' => 'moqbel', 'ar' => 'مقبل', 'en' => 'Moqbel', 'parent' => 'beni-suef-city', 'lat' => 29.0690, 'lng' => 31.0940],
            ['key' => 'el-mermah', 'ar' => 'المرماح', 'en' => 'El Mermah', 'parent' => 'beni-suef-city', 'lat' => 29.0650, 'lng' => 31.1010],
            ['key' => 'el-gezira', 'ar' => 'الجزيرة', 'en' => 'El Gezira', 'parent' => 'beni-suef-city', 'lat' => 29.0760, 'lng' => 31.1020],
            ['key' => 'el-hamraya', 'ar' => 'الحمرايا', 'en' => 'El Hamraya', 'parent' => 'beni-suef-city', 'lat' => 29.0580, 'lng' => 31.1120],
            ['key' => 'tazment-el-sharqiya', 'ar' => 'تزمنت الشرقية', 'en' => 'Tazment El Sharqiya', 'parent' => 'beni-suef-city', 'lat' => 29.0490, 'lng' => 31.0820],
            ['key' => 'barout', 'ar' => 'باروط', 'en' => 'Barout', 'parent' => 'beni-suef-city', 'lat' => 29.0380, 'lng' => 31.0760],
            ['key' => 'belefeia', 'ar' => 'بليفيا', 'en' => 'Belefeia', 'parent' => 'beni-suef-city', 'lat' => 29.0830, 'lng' => 31.0560],
            ['key' => 'sherif-pasha', 'ar' => 'شريف باشا', 'en' => 'Sherif Pasha', 'parent' => 'beni-suef-city', 'lat' => 29.0940, 'lng' => 31.0820],
            ['key' => 'baha', 'ar' => 'باها', 'en' => 'Baha', 'parent' => 'beni-suef-city', 'lat' => 29.1120, 'lng' => 31.0710],

            // مدينة بني سويف الجديدة
            ['key' => 'new-city-hay-1', 'ar' => 'الحي الأول بالمدينة الجديدة', 'en' => 'New City District 1', 'parent' => 'new-beni-suef', 'lat' => 29.0362, 'lng' => 31.1548],
            ['key' => 'new-city-hay-2', 'ar' => 'الحي الثاني بالمدينة الجديدة', 'en' => 'New City District 2', 'parent' => 'new-beni-suef', 'lat' => 29.0301, 'lng' => 31.1672],
            ['key' => 'new-city-hay-3', 'ar' => 'الحي الثالث بالمدينة الجديدة', 'en' => 'New City District 3', 'parent' => 'new-beni-suef', 'lat' => 29.0280, 'lng' => 31.1750],
            ['key' => 'new-city-hay-4', 'ar' => 'الحي الرابع بالمدينة الجديدة', 'en' => 'New City District 4', 'parent' => 'new-beni-suef', 'lat' => 29.0240, 'lng' => 31.1820],
            ['key' => 'new-city-hay-5', 'ar' => 'الحي الخامس بالمدينة الجديدة', 'en' => 'New City District 5', 'parent' => 'new-beni-suef', 'lat' => 29.0210, 'lng' => 31.1880],
            ['key' => 'new-city-industrial', 'ar' => 'المنطقة الصناعية بشرق النيل', 'en' => 'Industrial Zone', 'parent' => 'new-beni-suef', 'lat' => 29.0173, 'lng' => 31.1892],
            ['key' => 'ebny-betak', 'ar' => 'منطقة ابني بيتك', 'en' => 'Ebny Betak Zone', 'parent' => 'new-beni-suef', 'lat' => 29.0340, 'lng' => 31.1710],
            ['key' => 'social-housing', 'ar' => 'منطقة الإسكان الاجتماعي', 'en' => 'Social Housing Area', 'parent' => 'new-beni-suef', 'lat' => 29.0260, 'lng' => 31.1650],
            ['key' => 'universities-complex', 'ar' => 'مجمع الجامعات بشرق النيل', 'en' => 'East Nile Universities', 'parent' => 'new-beni-suef', 'lat' => 29.0410, 'lng' => 31.1620],

            // مركز الواسطى
            ['key' => 'el-wasta-city', 'ar' => 'مدينة الواسطى', 'en' => 'El Wasta City', 'parent' => 'el-wasta', 'lat' => 29.3390, 'lng' => 31.2110],
            ['key' => 'el-maymoun', 'ar' => 'الميمون', 'en' => 'El Maymoun', 'parent' => 'el-wasta', 'lat' => 29.2812, 'lng' => 31.1898],
            ['key' => 'qemen-el-arous', 'ar' => 'قمن العروس', 'en' => 'Qemen El Arous', 'parent' => 'el-wasta', 'lat' => 29.3140, 'lng' => 31.1750],
            ['key' => 'atwab', 'ar' => 'أطواب', 'en' => 'Atwab', 'parent' => 'el-wasta', 'lat' => 29.3620, 'lng' => 31.2180],
            ['key' => 'meidum', 'ar' => 'ميدوم', 'en' => 'Meidum', 'parent' => 'el-wasta', 'lat' => 29.3710, 'lng' => 31.1648],
            ['key' => 'abusir-el-malaq', 'ar' => 'أبو صير الملق', 'en' => 'Abusir El Malaq', 'parent' => 'el-wasta', 'lat' => 29.3480, 'lng' => 31.1120],
            ['key' => 'geziret-el-musaada', 'ar' => 'جزيرة المساعدة', 'en' => 'Geziret El Musaada', 'parent' => 'el-wasta', 'lat' => 29.3280, 'lng' => 31.2210],
            ['key' => 'zawyet-el-masloub', 'ar' => 'زاوية المصلوب', 'en' => 'Zawyet El Masloub', 'parent' => 'el-wasta', 'lat' => 29.3450, 'lng' => 31.2050],
            ['key' => 'beni-hodeir', 'ar' => 'بني حدير', 'en' => 'Beni Hodeir', 'parent' => 'el-wasta', 'lat' => 29.2590, 'lng' => 31.1820],
            ['key' => 'qosheisha', 'ar' => 'قشيشة', 'en' => 'Qosheisha', 'parent' => 'el-wasta', 'lat' => 29.3790, 'lng' => 31.2310],

            // مركز ناصر
            ['key' => 'nasser-city', 'ar' => 'مدينة ناصر (بوش)', 'en' => 'Nasser City (Bousch)', 'parent' => 'nasser', 'lat' => 29.1810, 'lng' => 31.0630],
            ['key' => 'ashment', 'ar' => 'أشمنت', 'en' => 'Ashment', 'parent' => 'nasser', 'lat' => 29.2150, 'lng' => 31.1210],
            ['key' => 'el-hamam', 'ar' => 'الحمام', 'en' => 'El Hamam', 'parent' => 'nasser', 'lat' => 29.2310, 'lng' => 31.1420],
            ['key' => 'dandeel', 'ar' => 'دنديل', 'en' => 'Dandeel', 'parent' => 'nasser', 'lat' => 29.1620, 'lng' => 31.0180],
            ['key' => 'bahbashin', 'ar' => 'بهبشين', 'en' => 'Bahbashin', 'parent' => 'nasser', 'lat' => 29.1950, 'lng' => 31.0250],
            ['key' => 'tansa-el-malaq', 'ar' => 'طنسا الملق', 'en' => 'Tansa El Malaq', 'parent' => 'nasser', 'lat' => 29.2480, 'lng' => 31.0820],
            ['key' => 'beni-adi', 'ar' => 'بني عدي', 'en' => 'Beni Adi', 'parent' => 'nasser', 'lat' => 29.1510, 'lng' => 31.0420],
            ['key' => 'el-zaytoun', 'ar' => 'الزيتون', 'en' => 'El Zaytoun', 'parent' => 'nasser', 'lat' => 29.1980, 'lng' => 31.1350],
            ['key' => 'el-riyad', 'ar' => 'الرياض', 'en' => 'El Riyad', 'parent' => 'nasser', 'lat' => 29.1720, 'lng' => 31.0850],

            // مركز إهناسيا
            ['key' => 'ihnasya-city', 'ar' => 'مدينة إهناسيا', 'en' => 'Ihnasya City', 'parent' => 'ihnasya', 'lat' => 29.0780, 'lng' => 30.9430],
            ['key' => 'nena', 'ar' => 'ننا', 'en' => 'Nena', 'parent' => 'ihnasya', 'lat' => 29.1210, 'lng' => 30.9320],
            ['key' => 'barawa', 'ar' => 'براوة', 'en' => 'Barawa', 'parent' => 'ihnasya', 'lat' => 29.0980, 'lng' => 30.9150],
            ['key' => 'qay', 'ar' => 'قاي', 'en' => 'Qay', 'parent' => 'ihnasya', 'lat' => 29.0520, 'lng' => 30.9020],
            ['key' => 'sedment-el-gabal', 'ar' => 'سدمنت الجبل', 'en' => 'Sedment El Gabal', 'parent' => 'ihnasya', 'lat' => 29.0410, 'lng' => 30.8750],
            ['key' => 'el-noweira', 'ar' => 'النويرة', 'en' => 'El Noweira', 'parent' => 'ihnasya', 'lat' => 29.1150, 'lng' => 30.8850],
            ['key' => 'el-awawna', 'ar' => 'العواونة', 'en' => 'El Awawna', 'parent' => 'ihnasya', 'lat' => 29.1350, 'lng' => 30.9120],
            ['key' => 'mayana', 'ar' => 'ميانة', 'en' => 'Mayana', 'parent' => 'ihnasya', 'lat' => 29.0680, 'lng' => 30.8890],
            ['key' => 'qolha', 'ar' => 'قلها', 'en' => 'Qolha', 'parent' => 'ihnasya', 'lat' => 29.0820, 'lng' => 30.8650],
            ['key' => 'el-meseid', 'ar' => 'المسيد', 'en' => 'El Meseid', 'parent' => 'ihnasya', 'lat' => 29.0910, 'lng' => 30.9620],

            // مركز ببا
            ['key' => 'biba-city', 'ar' => 'مدينة ببا', 'en' => 'Biba City', 'parent' => 'biba', 'lat' => 28.9210, 'lng' => 30.9850],
            ['key' => 'saft-rashin', 'ar' => 'صفط راشين', 'en' => 'Saft Rashin', 'parent' => 'biba', 'lat' => 28.9450, 'lng' => 30.9320],
            ['key' => 'qambash-el-hamra', 'ar' => 'قمبش الحمراء', 'en' => 'Qambash El Hamra', 'parent' => 'biba', 'lat' => 28.8950, 'lng' => 30.9520],
            ['key' => 'sods-el-omara', 'ar' => 'سدس الأمراء', 'en' => 'Sods El Omara', 'parent' => 'biba', 'lat' => 28.8820, 'lng' => 30.9250],
            ['key' => 'geziret-biba', 'ar' => 'جزيرة ببا', 'en' => 'Geziret Biba', 'parent' => 'biba', 'lat' => 28.9250, 'lng' => 30.9950],
            ['key' => 'tansa-beni-malo', 'ar' => 'طنسا بني مالو', 'en' => 'Tansa Beni Malo', 'parent' => 'biba', 'lat' => 28.9550, 'lng' => 30.9780],
            ['key' => 'taha-el-bisha', 'ar' => 'طحا البيشة', 'en' => 'Taha El Bisha', 'parent' => 'biba', 'lat' => 28.9680, 'lng' => 30.9520],
            ['key' => 'herbeshent', 'ar' => 'هربشنت', 'en' => 'Herbeshent', 'parent' => 'biba', 'lat' => 28.9120, 'lng' => 30.9380],
            ['key' => 'beni-qasim', 'ar' => 'بني قاسم', 'en' => 'Beni Qasim', 'parent' => 'biba', 'lat' => 28.9320, 'lng' => 30.9650],
            ['key' => 'fazara', 'ar' => 'فزارة', 'en' => 'Fazara', 'parent' => 'biba', 'lat' => 28.8750, 'lng' => 30.9620],

            // مركز سمسطا
            ['key' => 'sumusta-city', 'ar' => 'مدينة سمسطا', 'en' => 'Sumusta City', 'parent' => 'sumusta', 'lat' => 28.9110, 'lng' => 30.8510],
            ['key' => 'badhal', 'ar' => 'بدهل', 'en' => 'Badhal', 'parent' => 'sumusta', 'lat' => 28.9420, 'lng' => 30.8750],
            ['key' => 'mazoura', 'ar' => 'مازورة', 'en' => 'Mazoura', 'parent' => 'sumusta', 'lat' => 28.9350, 'lng' => 30.8250],
            ['key' => 'dashtout', 'ar' => 'دشطوط', 'en' => 'Dashtout', 'parent' => 'sumusta', 'lat' => 28.8850, 'lng' => 30.8120],
            ['key' => 'el-shantour', 'ar' => 'الشنطور', 'en' => 'El Shantour', 'parent' => 'sumusta', 'lat' => 28.8750, 'lng' => 30.8650],
            ['key' => 'el-ouna', 'ar' => 'العونة', 'en' => 'El Ouna', 'parent' => 'sumusta', 'lat' => 28.9250, 'lng' => 30.8850],
            ['key' => 'el-mahmoudia', 'ar' => 'المحمودية', 'en' => 'El Mahmoudia', 'parent' => 'sumusta', 'lat' => 28.8950, 'lng' => 30.8350],
            ['key' => 'kafr-beni-ali', 'ar' => 'كفر بني علي', 'en' => 'Kafr Beni Ali', 'parent' => 'sumusta', 'lat' => 28.9180, 'lng' => 30.8620],
            ['key' => 'beni-hella', 'ar' => 'بني حلة', 'en' => 'Beni Hella', 'parent' => 'sumusta', 'lat' => 28.8820, 'lng' => 30.8850],

            // مركز الفشن
            ['key' => 'fashn-city', 'ar' => 'مدينة الفشن', 'en' => 'El Fashn City', 'parent' => 'fashn', 'lat' => 28.7950, 'lng' => 30.8890],
            ['key' => 'el-fant', 'ar' => 'الفنت', 'en' => 'El Fant', 'parent' => 'fashn', 'lat' => 28.8350, 'lng' => 30.8920],
            ['key' => 'telet', 'ar' => 'تلت', 'en' => 'Telet', 'parent' => 'fashn', 'lat' => 28.7750, 'lng' => 30.8720],
            ['key' => 'iqfehas', 'ar' => 'إقفهص', 'en' => 'Iqfehas', 'parent' => 'fashn', 'lat' => 28.8120, 'lng' => 30.9150],
            ['key' => 'shenra', 'ar' => 'شنرا', 'en' => 'Shenra', 'parent' => 'fashn', 'lat' => 28.7520, 'lng' => 30.8520],
            ['key' => 'dalhans', 'ar' => 'دلهانس', 'en' => 'Dalhans', 'parent' => 'fashn', 'lat' => 28.8250, 'lng' => 30.8450],
            ['key' => 'saft-el-khemar', 'ar' => 'صفط الخمار', 'en' => 'Saft El Khemar', 'parent' => 'fashn', 'lat' => 28.7890, 'lng' => 30.8650],
            ['key' => 'beni-saleh', 'ar' => 'بني صالح', 'en' => 'Beni Saleh', 'parent' => 'fashn', 'lat' => 28.7650, 'lng' => 30.8950],
            ['key' => 'el-shaqar', 'ar' => 'الشقر', 'en' => 'El Shaqar', 'parent' => 'fashn', 'lat' => 28.8450, 'lng' => 30.8750],
            ['key' => 'el-gafadoun', 'ar' => 'الجفادون', 'en' => 'El Gafadoun', 'parent' => 'fashn', 'lat' => 28.7450, 'lng' => 30.8820],
        ];
    }

    /** Real landmarks used to make descriptions feel local, in both locales. */
    public static function landmarks(): array
    {
        return [
            ['ar' => 'جامعة بني سويف', 'en' => 'Beni Suef University'],
            ['ar' => 'مستشفى بني سويف الجامعي', 'en' => 'Beni Suef University Hospital'],
            ['ar' => 'كورنيش النيل', 'en' => 'the Nile Corniche'],
            ['ar' => 'ميدان الشهداء', 'en' => 'Martyrs Square'],
            ['ar' => 'محطة قطار بني سويف', 'en' => 'Beni Suef railway station'],
            ['ar' => 'المنطقة الصناعية', 'en' => 'the industrial zone'],
            ['ar' => 'مدينة بني سويف الجديدة', 'en' => 'New Beni Suef City'],
            ['ar' => 'كوبري بني سويف العلوي', 'en' => 'the Beni Suef flyover'],
            ['ar' => 'الحديقة الدولية', 'en' => 'the International Park'],
            ['ar' => 'مجمع المحاكم', 'en' => 'the courts complex'],
        ];
    }

    /** @return list<string> Arabic landmark names only. */
    public static function landmarkNamesAr(): array
    {
        return array_column(self::landmarks(), 'ar');
    }

    /** Street names used in addresses, in both locales. */
    public static function streets(): array
    {
        return [
            ['ar' => 'شارع صلاح سالم', 'en' => 'Salah Salem Street'],
            ['ar' => 'شارع أحمد عرابي', 'en' => 'Ahmed Orabi Street'],
            ['ar' => 'شارع بورسعيد', 'en' => 'Port Said Street'],
            ['ar' => 'شارع النيل', 'en' => 'Nile Street'],
            ['ar' => 'شارع الجمهورية', 'en' => 'El Gomhoreya Street'],
            ['ar' => 'شارع سعد زغلول', 'en' => 'Saad Zaghloul Street'],
            ['ar' => 'شارع التحرير', 'en' => 'El Tahrir Street'],
            ['ar' => 'شارع مصطفى كامل', 'en' => 'Mostafa Kamel Street'],
            ['ar' => 'شارع عمر بن الخطاب', 'en' => 'Omar Ibn El Khattab Street'],
            ['ar' => 'شارع المدارس', 'en' => 'El Madaris Street'],
            ['ar' => 'شارع الكورنيش', 'en' => 'Corniche Street'],
            ['ar' => 'شارع المحطة', 'en' => 'El Mahatta Street'],
        ];
    }

    /** Residential compound / building names. */
    public static function compounds(): array
    {
        return [
            ['ar' => 'كمبوند النيل جاردنز', 'en' => 'Nile Gardens Compound'],
            ['ar' => 'كمبوند بني سويف هايتس', 'en' => 'Beni Suef Heights'],
            ['ar' => 'كمبوند الواسطى ريزيدنس', 'en' => 'Wasta Residence'],
            ['ar' => 'كمبوند الياسمين', 'en' => 'Yasmin Compound'],
            ['ar' => 'كمبوند النخيل', 'en' => 'Palm Compound'],
            ['ar' => 'أبراج النيل الإداري', 'en' => 'Nile Towers'],
            ['ar' => 'كمبوند جرين فيو', 'en' => 'Green View Compound'],
            ['ar' => 'كمبوند بيت الوطن', 'en' => 'Beit El Watan Compound'],
            ['ar' => 'مجمع الأندلس السكني', 'en' => 'Andalus Residence'],
            ['ar' => 'أبراج السلام', 'en' => 'El Salam Towers'],
        ];
    }

    /** Finish levels (real Egyptian market vocabulary). */
    public static function finishes(): array
    {
        return [
            ['ar' => 'تشطيب سوبر لوكس', 'en' => 'Super-lux finishing'],
            ['ar' => 'تشطيب لوكس', 'en' => 'Lux finishing'],
            ['ar' => 'تشطيب نصف لوكس', 'en' => 'Semi-lux finishing'],
            ['ar' => 'نصف تشطيب', 'en' => 'Semi-finished'],
            ['ar' => 'متشطبة بالكامل', 'en' => 'Fully finished'],
            ['ar' => 'على المحارة', 'en' => 'Core and shell'],
        ];
    }

    /** Construction years that match the mirror data. */
    public static function buildYears(): array
    {
        return [2016, 2017, 2018, 2019, 2020, 2021, 2022, 2023, 2024];
    }

    /**
     * Cities keyed by their slug for quick lookups.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function citiesByKey(): array
    {
        return collect(self::cities())->keyBy('key')->all();
    }

    /**
     * Districts grouped by their parent city key.
     *
     * @return array<string, list<array<string, mixed>>>
     */
    public static function districtsByParent(): array
    {
        $grouped = [];

        foreach (self::districts() as $district) {
            $grouped[$district['parent']][] = $district;
        }

        return $grouped;
    }

    /**
     * All locations in the order they should be created: governorate first,
     * then the cities, then the neighbourhoods.
     *
     * @return list<array<string, mixed>>
     */
    public static function allLocations(): array
    {
        $rows = [[
            'key' => 'beni-suef',
            'ar' => self::GOVERNORATE['ar'],
            'en' => self::GOVERNORATE['en'],
            'parent' => null,
            'type' => 'governorate',
            'lat' => self::GOVERNORATE['lat'],
            'lng' => self::GOVERNORATE['lng'],
        ]];

        foreach (self::cities() as $city) {
            $rows[] = [
                'key' => $city['key'],
                'ar' => $city['ar'],
                'en' => $city['en'],
                'parent' => 'beni-suef',
                'type' => 'city',
                'lat' => $city['lat'],
                'lng' => $city['lng'],
            ];
        }

        foreach (self::districts() as $district) {
            $rows[] = [
                'key' => $district['key'],
                'ar' => $district['ar'],
                'en' => $district['en'],
                'parent' => $district['parent'],
                'type' => 'district',
                'lat' => $district['lat'],
                'lng' => $district['lng'],
            ];
        }

        return $rows;
    }

    /** True when a coordinate sits inside the governorate bounding box. */
    public static function containsPoint(float $lat, float $lng): bool
    {
        return $lat >= self::BOUNDS['lat_min'] && $lat <= self::BOUNDS['lat_max']
            && $lng >= self::BOUNDS['lng_min'] && $lng <= self::BOUNDS['lng_max'];
    }

    /**
     * Deterministic small offset so several listings in the same district do
     * not stack on one pixel, while staying inside the governorate.
     *
     * @return array{0: float, 1: float}
     */
    public static function jitter(float $lat, float $lng, int $seed): array
    {
        $angle = ($seed % 24) * 15 * M_PI / 180;
        $radius = 0.004 + (($seed % 7) * 0.0016);

        $jLat = round($lat + ($radius * cos($angle)), 7);
        $jLng = round($lng + ($radius * sin($angle) * 1.15), 7);

        if (! self::containsPoint($jLat, $jLng)) {
            return [round($lat, 7), round($lng, 7)];
        }

        return [$jLat, $jLng];
    }

    /**
     * Price per square metre for a location + unit kind.
     * kind: apartment | villa | commercial | land
     */
    public static function pricePerMetre(string $cityKey, string $kind = 'apartment', bool $forRent = false): int
    {
        $city = self::citiesByKey()[$cityKey] ?? self::cities()[0];

        if ($forRent) {
            return (int) round(($city['rent_min'] + $city['rent_max']) / 2);
        }

        $base = (int) round(($city['price_min'] + $city['price_max']) / 2);

        return (int) round($base * match ($kind) {
            'villa' => 1.45,
            'commercial' => 1.35,
            'land' => 0.55,
            default => 1.0,
        });
    }

    /**
     * واقعي: الإيجار الشهري بالجنيه المصري بحسب المدينة ونوع الوحدة وعدد الغرف.
     *
     * @return int|null null when the unit kind is never rented (land).
     */
    public static function monthlyRent(string $cityKey, string $kind = 'apartment', ?int $bedrooms = null, int $seed = 0): ?int
    {
        if ($kind === 'land') {
            return null;
        }

        $city = self::citiesByKey()[$cityKey] ?? self::cities()[0];
        $base = (int) round(($city['rent_min'] + $city['rent_max']) / 2);

        $multiplier = match ($kind) {
            'villa' => 2.4,
            'commercial' => 1.7,
            default => $bedrooms ? max(0.85, 0.5 + ($bedrooms * 0.16)) : 1.0,
        };

        $variation = 0.93 + (($seed % 7) * 0.02);
        $rent = (int) round(($base * $multiplier * $variation) / 100) * 100;

        return max(1500, $rent);
    }
}
