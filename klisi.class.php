<?php

class Klisi {
    private $onoma;
    private $gender;
    private $exceptions;
    private $kat2;
    private $kat1;
    private $name1;
    private $name2;

    function __construct( $onoma = null, $gender = null){
        if ( $onoma ) {
            $this->onoma = $onoma;

            $this->gender = $gender;
            if (!$gender) $this->gender = 1;

            $this->kat1 = mb_substr($this->onoma, -1);
            $this->kat2 = mb_substr($this->onoma, -2);
            $this->name1 = mb_substr($this->onoma, 0, (mb_strlen($this->onoma) - 1));
            $this->name2 = mb_substr($this->onoma, 0, (mb_strlen($this->onoma) - 2));

            //εαν το ονομα τελειώνει σε συγκεκριμένες καταλήξεις κάνουμε παραδοχή ότι το φύλλο είναι κορίτσι
            if (in_array($this->kat1, ['Α', 'Η', 'Ω'])) $this->gender = 2; //ΚΑΤΕΡΙΝΑ, ΔΑΝΑΗ, ΜΥΡΤΩ
            if (in_array($this->kat2, ['ΙΣ'])) $this->gender = 2; //ΑΜΑΡΥΛΛΙΣ
        }

        // αφαιρούμε του τόνους από κεφαλαίους χαρακτήρες εάν υπάρχουν
        $this->onoma = strtr($this->onoma, 'ΆΈΉΊΌΎΏ', 'ΑΕΗΙΟΥΩ');

        // φορτώνουμε τις εξαιρέσεις
        $json = file_get_contents(__DIR__ . '/rules/exceptions.json');
        $this->exceptions = (array) json_decode($json);
    }

    /**
     * Η συνάρτηση αυτή επιτρέφει πίνακα με τη μορφή ['geniki' => 'ΓΕΝΙΚΗ ΟΝΟΜΑΤΟΣ','aitiatiki' => 'ΑΙΤΙΑΤΙΚΗ ΟΝΟΜΑΤΟΣ']
     * για το δοθέν όνομα.
     * @return array|string[]
     */
    function getPtoseis() {

        $this->onoma = trim($this->onoma);

        // εάν το ονομα αποτελείται από πολλές λέξεις σπάμε την ονομασία σε επιμέρους λέξεις. Υποστηρίζονται οι διαχωριστές '-', ' ' και ' - '
        $separator = '';
        if (strpos($this->onoma,' ') !== false) $separator = ' ';
        if (strpos($this->onoma,'-') !== false) $separator = '-';
        if (strpos($this->onoma,' - ') !== false) $separator = ' - ';

        if ($separator) {
            $parts = explode($separator,$this->onoma);
            $aitiatiki = '';
            $geniki = '';
            $i = 0;
            foreach ($parts as $key=>$val) {
                if ($i!=0) {
                    $aitiatiki .= $separator;
                    $geniki .= $separator;
                }
                $i++;
                $val = rtrim($val);
                $tmpKlisi = new Klisi($val);
                $single = $tmpKlisi->getPtoseisSingle();
                $geniki .= $single['geniki'];
                $aitiatiki .= $single['aitiatiki'];
            }
            return [
                'geniki' => $geniki,
                'aitiatiki' => $aitiatiki,
            ];
        }

        // εάν δεν υπάρχει διαχωριστής τότε επιστρέφουμε την κλίση μόνο μίας λέξης
        return $this->getPtoseisSingle();
    }

    /**
     * Η συνάρτηση αυτή επιτρέφει πίνακα με τη μορφή ['geniki' => 'ΓΕΝΙΚΗ ΟΝΟΜΑΤΟΣ','aitiatiki' => 'ΑΙΤΙΑΤΙΚΗ ΟΝΟΜΑΤΟΣ']
     * για το δοθέν όνομα που περιλαμβάνει μόνο μία λέξη
     * @return array|string[]
     */
    function getPtoseisSingle() {

        //εάν το όνομα υπάρχει στον πίνακα των εξαιρέσεων τότε επιστρέφουμε κατ' ευθείαν την εξαίρεση
        if (array_key_exists($this->onoma,$this->exceptions)) {
            return [
                'geniki' => $this->exceptions[$this->onoma][0],
                'aitiatiki' => $this->exceptions[$this->onoma][1],
            ];
        }
        if ($this->gender == 1) {
            if (in_array($this->kat2, ['ΗΣ','ΑΣ'])) { //ΠΑΝΑΓΙΩΤΗΣ
                return [
                    'geniki' => $this->name1,
                    'aitiatiki' => $this->name1
                ];
            }
            if ($this->kat2 == 'ΟΣ') { //ΣΠΥΡΟΣ
                return [
                    'geniki' => $this->name1.'Υ',
                    'aitiatiki' => $this->name1
                ];
            }
            if (in_array($this->kat2,['ΕΣ'])) {
                return [
                    'geniki' => $this->name1,
                    'aitiatiki' => $this->name1
                ];
            }
            if (in_array($this->kat2, ['ΩΝ','ΩΡ'])) {//ΑΓΑΜΕΜΝΩΝ, ΙΑΣΩΝ, //ΕΚΤΩΡ, ΝΕΣΤΩΡ,
                return [
                    'geniki' => $this->onoma.'Α',
                    'aitiatiki' => $this->onoma.'Α',
                ];
            }
            if ($this->kat2 == 'ΥΣ') { //ΑΧΙΛΕΥΣ, ΠΕΡΣΕΥΣ
                return [
                    'geniki' => $this->name2.'Α',
                    'aitiatiki' => $this->name2.'Α'
                ];
            }
        }
        if ($this->gender == 2) {
            if (in_array($this->kat1, ['Α', 'Η'])) { //ΚΑΤΕΡΙΝΑ, ΕΛΕΝΗ
                return [
                    'geniki' => $this->onoma . 'Σ',
                    'aitiatiki' => $this->onoma
                ];
            }
            if ($this->kat2 == 'ΙΣ') { //ΑΜΑΡΥΛΛΙΣ
                return [
                    'geniki' => $this->name1 . 'ΔΟΣ',
                    'aitiatiki' => $this->name1 .'ΔΑ'
                ];
            }
            if (in_array($this->kat1, ['Ω'])) { //ΜΥΡΤΩ
                return [
                    'geniki' => $this->name1 . 'ΟΥΣ',
                    'aitiatiki' => $this->onoma
                ];
            }

        }
        // εαν κανένας κανόνας δε κάνει εφαρμογή επέστρεψε το ίδιο όνομα
        return [
            'geniki' => $this->onoma,
            'aitiatiki' => $this->onoma
        ];
    }
}