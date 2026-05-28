/**
 * Province / city / barangay data for areas near General Santos City (SOCCSKSARGEN).
 * Used by registration, profile, and admin user forms.
 */
(function (global) {
    const cityPostalCodes = {
        'General Santos City': '9500',
        'Koronadal City': '9506',
        'Banga': '9511',
        'Lake Sebu': '9514',
        'Norala': '9508',
        'Polomolok': '9504',
        'Santo Nino': '9509',
        'Surallah': '9512',
        'Tampakan': '9507',
        'Tantangan': '9510',
        'Tupi': '9505',
        "T'boli": '9513',
        'Alabel': '9501',
        'Glan': '9517',
        'Kiamba': '9514',
        'Maasim': '9502',
        'Maitum': '9515',
        'Malapatan': '9516',
        'Malungon': '9503',
        'Tacurong City': '9800',
        'Isulan': '9805'
    };

    const provinces = {
        'South Cotabato': {
            'General Santos City': [
                'Apopong', 'Baluan', 'Bawing', 'Buayan', 'Bula', 'Calumpang', 'City Heights',
                'Conel', 'Dadiangas East', 'Dadiangas North', 'Dadiangas South', 'Dadiangas West',
                'Fatima', 'Katangawan', 'Labangal', 'Lagao', 'Ligaya', 'Mabuhay', 'Olympog',
                'San Isidro', 'San Jose', 'Siguel', 'Sinawal', 'Tambler', 'Tinagacan', 'Upper Labay'
            ],
            'Koronadal City': [
                'Avancena', 'Cacub', 'Caloocan', 'Carpenter Hill', 'Concepcion',
                'General Paulino Santos', 'Mabini', 'Magsaysay', 'Morales', 'San Isidro',
                'Santa Cruz', 'Zone I', 'Zone II', 'Zone III', 'Zone IV'
            ],
            'Polomolok': [
                'Cannery Site', 'Glamang', 'Kinilis', 'Koronadal Proper', 'Landan', 'Lapu', 'Lumakil',
                'Magsaysay', 'Maligo', 'Pagalungan', 'Palkan', 'Poblacion', 'Rubber', 'Silway 7',
                'Silway 8', 'Sumbakil'
            ],
            'Tupi': [
                'Acmonan', 'Bololmala', 'Bunao', 'Cebuano', 'Crossing Rubber', 'Kablon', 'Kalkam',
                'Linan', 'Lunen', 'Miasong', 'Palian', 'Poblacion', 'Polonuling', 'Simbo', 'Tubeng'
            ],
            'Banga': [
                'Benitez', 'Cabudian', 'Cabuling', 'Cinco', 'Derilon', 'El Nonok', 'Improgo Village',
                'Kusan', 'Lam-apos', 'Lamba', 'Lambingi', 'Lampari', 'Liwanay', 'Malaya',
                'Punong Grande', 'Rang-ay', 'Reyes', 'Rizal', 'Rizal Poblacion', 'San Jose',
                'San Vicente', 'Yangco Poblacion'
            ],
            'Norala': [
                'Benigno Aquino, Jr.', 'Dumaguil', 'Esperanza', 'Kibid', 'Lapuz', 'Liberty',
                'Lopez Jaena', 'Matapol', 'Poblacion', 'Puti', 'San Jose', 'San Miguel', 'Simsiman', 'Tinago'
            ],
            'Surallah': [
                'Buenavista', 'Canahay', 'Centrala', 'Colongulo', 'Dajay', 'Duengas', 'Lambontong',
                'Lamian', 'Lamsugod', 'Libertad', 'Little Baguio', 'Moloy', 'Naci', 'Talahik',
                'Tubiala', 'Upper Sepaka', 'Veterans'
            ],
            'Tantangan': [
                'Bukay Pait', 'Cabuling', 'Dumadalig', 'Libas', 'Magon', 'Maibo', 'Mangilala',
                'New Cuyapo', 'New Iloilo', 'New Lambunao', 'Poblacion', 'San Felipe', 'Tinongcop'
            ],
            'Tampakan': [
                'Albagan', 'Buto', 'Danlag', 'Kipalbig', 'Lambayong', 'Lampitak', 'Liberty',
                'Maltana', 'Palo', 'Poblacion', 'Pula-bato', 'San Isidro', 'Santa Cruz', 'Tablu'
            ],
            'Santo Nino': [
                'Ambalgan', 'Guinsang-an', 'Katipunan', 'Manuel Roxas', 'Panay', 'Poblacion',
                'Sajaneba', 'San Isidro', 'San Vicente', 'Teresita'
            ],
            'Lake Sebu': [
                'Bacdulong', 'Denlag', 'Halilan', 'Hanoon', 'Klubi', 'Lake Lahit', 'Lamcade',
                'Lamdalag', 'Lamfugon', 'Lamlahak', 'Lower Maculan', 'Luhib', 'Ned', 'Poblacion',
                'Siluton', 'Takunel', 'Talisay', 'Tasiman', 'Upper Maculan'
            ],
            "T'boli": [
                'Aflek', 'Afus', 'Basag', 'Datal Bob', 'Desawo', 'Dlanag', 'Edwards', 'Kematu',
                'Laconon', 'Lambangan', 'Lambuling', 'Lamhako', 'Lamsalome', 'Lemsnolon', 'Maan',
                'Malugong', 'Mongocayo', 'New Dumangas', 'Poblacion', 'Salacafe', 'Sinolon',
                'Talcon', 'Talufo', "T'bolok", 'Tudok'
            ]
        },
        'Sarangani': {
            'Alabel': [
                'Alegria', 'Bagacay', 'Baluntay', 'Domolok', 'Kawas', 'Maribulan', 'Pag-asa',
                'Paraiso', 'Poblacion', 'Spring', 'Tokawal'
            ],
            'Glan': [
                'Baliton', 'Batulaki', 'Big Margus', 'Burias', 'Calabanit', 'Cross', 'Datal Bukay',
                'E. Alegado', 'Gumasa', 'Kapatan', 'Lago', 'Poblacion', 'Rio del Pilar', 'San Jose',
                'Taluya', 'Tangisan', 'Upper Klinan'
            ],
            'Malungon': [
                'Alpabel', 'Banate', 'Datal Batong', 'Datal Bila', 'Datal Tampal', 'Kawayan',
                'Lower Mainit', 'Malungon Gamay', 'Poblacion', 'San Juan', 'Tamban', 'Upper Mainit'
            ],
            'Kiamba': [
                'Badtasan', 'Gasi', 'Kling', 'Mabay', 'Maligang', 'Ned', 'Poblacion', 'Salidan',
                'Suli', 'Tablao', 'Talukpod', 'Ticulab'
            ],
            'Maasim': [
                'Amsipit', 'Balesmic', 'Colon', 'Daliao', 'Kabatiol', 'Kablacan', 'Lumatil', 'Malbang',
                'Nomoh', 'Olvia', 'Poblacion', 'Seven Hills', 'Tinoto', 'Tuburan'
            ],
            'Maitum': [
                'Kiambing', 'Mabay', 'Maltana', 'New La Union', 'Old Poblacion', 'Pamantingan', 'Pangi',
                'Pinol', 'Poblacion', 'Sison', 'Ticulab', 'Upo', 'Wali', 'Yabay'
            ],
            'Malapatan': [
                'Daan Suyan', 'Kihan', 'Kinag', 'Libo', 'Lun Masla', 'Lun Pequeño', 'Malapatan',
                'Municipal', 'Poblacion', 'Sapu Padidu', 'Sulit', 'Tuyan'
            ]
        },
        'Sultan Kudarat': {
            'Tacurong City': [
                'Baras', 'Buenaflor', 'Calean', 'Carmen', "D'Ledesma", 'Virginia Griño', 'Kalandagan',
                'Lancheta', 'Enrique JC Montilla', 'New Isabela', 'New Lagao', 'New Passi', 'Poblacion',
                'Rajah Nuda', 'San Antonio', 'San Emmanuel', 'San Pablo', 'San Rafael', 'Tina', 'Upper Katungal'
            ],
            'Isulan': [
                'Bambad', 'Bual', 'Dansuli', "D'Lotilla", 'Impao', 'Kalawag I', 'Kalawag II', 'Kalawag III',
                'Kenram', 'Kolambog', 'Kudanding', 'Lagandang', 'Laguilayan', 'Mapantig',
                'New Pangasinan', 'Sampao', 'Tayugo'
            ]
        }
    };

    function getAddressData() {
        const data = {};
        Object.keys(provinces).forEach(function (province) {
            data[province] = provinces[province];
        });
        return data;
    }

    function getProvinceNames() {
        return Object.keys(provinces).sort();
    }

    global.GensanRegionAddress = {
        defaultProvince: 'South Cotabato',
        defaultCity: 'General Santos City',
        cityPostalCodes: cityPostalCodes,
        provinces: provinces,
        getAddressData: getAddressData,
        getProvinceNames: getProvinceNames
    };
})(typeof window !== 'undefined' ? window : this);
