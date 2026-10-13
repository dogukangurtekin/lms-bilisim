/* Python Quiz Lab — 100 bölümlük müfredat (otomatik üretildi: tools/learn-levels/silent-teacher.gen.mjs, elle düzenlemeyin). */
const CURRICULUM = {
 "easy": [
  {
   "id": "easy_1",
   "title": "Değişkenler",
   "lesson": "Değişken, bir değere verilen isimdir. a = 5 yazarsan a artık 5’tir.",
   "questions": [
    {
     "code": "k = 10\nk + 8",
     "prompt": "Son satırın sonucu nedir?",
     "options": [
      "16",
      "17",
      "19",
      "20",
      "18",
      "36",
      "?!?"
     ],
     "answer": "18"
    },
    {
     "code": "k = 11\nk + 4",
     "prompt": "Son satırın sonucu nedir?",
     "options": [
      "17",
      "13",
      "15",
      "16",
      "30",
      "14",
      "?!?"
     ],
     "answer": "15"
    },
    {
     "code": "y = 5\ny + 1",
     "prompt": "Son satırın sonucu nedir?",
     "options": [
      "5",
      "8",
      "12",
      "4",
      "7",
      "6",
      "?!?"
     ],
     "answer": "6"
    }
   ]
  },
  {
   "id": "easy_2",
   "title": "Değeri Değiştirme",
   "lesson": "Değişkene yeni değer atarsan eski değer silinir; son atama geçerlidir.",
   "questions": [
    {
     "code": "y = 8\ny = 13\ny + 1",
     "prompt": "Son satırın sonucu nedir?",
     "options": [
      "14",
      "16",
      "9",
      "15",
      "13",
      "21",
      "?!?"
     ],
     "answer": "14"
    },
    {
     "code": "k = 8\nk = 16\nk + 5",
     "prompt": "Son satırın sonucu nedir?",
     "options": [
      "23",
      "20",
      "21",
      "24",
      "22",
      "13",
      "?!?"
     ],
     "answer": "21"
    },
    {
     "code": "k = 5\nk = 17\nk + 1",
     "prompt": "Son satırın sonucu nedir?",
     "options": [
      "22",
      "17",
      "20",
      "18",
      "19",
      "6",
      "?!?"
     ],
     "answer": "18"
    }
   ]
  },
  {
   "id": "easy_3",
   "title": "Toplama ve Çıkarma",
   "lesson": "+ ve - işlemleri sayıları toplar ve çıkarır; sonuç değişkenlerle hesaplanır.",
   "questions": [
    {
     "code": "x = 25\ny = 3\nx - y",
     "prompt": "x - y sonucu nedir?",
     "options": [
      "23",
      "44",
      "24",
      "20",
      "21",
      "22",
      "?!?"
     ],
     "answer": "22"
    },
    {
     "code": "x = 19\ny = 3\nx + y",
     "prompt": "x + y sonucu nedir?",
     "options": [
      "21",
      "20",
      "24",
      "44",
      "23",
      "22",
      "?!?"
     ],
     "answer": "22"
    },
    {
     "code": "x = 30\ny = 3\nx - y",
     "prompt": "x - y sonucu nedir?",
     "options": [
      "27",
      "25",
      "54",
      "28",
      "29",
      "26",
      "?!?"
     ],
     "answer": "27"
    }
   ]
  },
  {
   "id": "easy_4",
   "title": "Çarpma ve Bölme",
   "lesson": "* çarpar, / böler. Bölme sonucu her zaman ondalıklı (float) çıkar: 10 / 2 → 5.0.",
   "questions": [
    {
     "code": "7 * 3",
     "prompt": "Sonuc nedir?",
     "options": [
      "23",
      "21",
      "42",
      "20",
      "22",
      "19",
      "?!?"
     ],
     "answer": "21"
    },
    {
     "code": "5 * 9",
     "prompt": "Sonuc nedir?",
     "options": [
      "44",
      "90",
      "43",
      "47",
      "46",
      "45",
      "?!?"
     ],
     "answer": "45"
    },
    {
     "code": "2 * 7",
     "prompt": "Sonuc nedir?",
     "options": [
      "28",
      "15",
      "13",
      "16",
      "14",
      "12",
      "?!?"
     ],
     "answer": "14"
    }
   ]
  },
  {
   "id": "easy_5",
   "title": "Tam Bölme ve Kalan",
   "lesson": "// tam bölümü, % kalanı verir. 17 // 5 → 3, 17 % 5 → 2.",
   "questions": [
    {
     "code": "18 // 5",
     "prompt": "// tam bölmenin bölümünü verir. Sonuç nedir?",
     "options": [
      "2",
      "4",
      "1",
      "5",
      "3",
      "6",
      "?!?"
     ],
     "answer": "3"
    },
    {
     "code": "14 % 6",
     "prompt": "% işlemi bölümden kalanı verir. Sonuç nedir?",
     "options": [
      "2",
      "3",
      "4",
      "1",
      "0",
      "12",
      "?!?"
     ],
     "answer": "2"
    },
    {
     "code": "31 % 6",
     "prompt": "% işlemi bölümden kalanı verir. Sonuç nedir?",
     "options": [
      "0",
      "3",
      "2",
      "-1",
      "1",
      "5",
      "?!?"
     ],
     "answer": "1"
    }
   ]
  },
  {
   "id": "easy_6",
   "title": "Üs Alma",
   "lesson": "** üs alır: 2 ** 3 → 8.",
   "questions": [
    {
     "code": "2 ** 3",
     "prompt": "** üs alma işlemidir. Sonuç nedir?",
     "options": [
      "9",
      "10",
      "16",
      "7",
      "6",
      "8",
      "?!?"
     ],
     "answer": "8"
    },
    {
     "code": "4 ** 4",
     "prompt": "** üs alma işlemidir. Sonuç nedir?",
     "options": [
      "255",
      "16",
      "258",
      "257",
      "256",
      "254",
      "?!?"
     ],
     "answer": "256"
    },
    {
     "code": "3 ** 2",
     "prompt": "** üs alma işlemidir. Sonuç nedir?",
     "options": [
      "7",
      "6",
      "10",
      "11",
      "9",
      "8",
      "?!?"
     ],
     "answer": "9"
    }
   ]
  },
  {
   "id": "easy_7",
   "title": "İşlem Önceliği",
   "lesson": "Önce çarpma ve bölme, sonra toplama ve çıkarma yapılır.",
   "questions": [
    {
     "code": "2 + 2 * 7",
     "prompt": "Önce çarpma/bölme yapılır. Sonuç nedir?",
     "options": [
      "16",
      "15",
      "18",
      "17",
      "14",
      "32",
      "?!?"
     ],
     "answer": "16"
    },
    {
     "code": "16 - 4 * 7",
     "prompt": "Önce çarpma/bölme yapılır. Sonuç nedir?",
     "options": [
      "-13",
      "-12",
      "-11",
      "-24",
      "-10",
      "-14",
      "?!?"
     ],
     "answer": "-12"
    },
    {
     "code": "18 - 6 * 3",
     "prompt": "Önce çarpma/bölme yapılır. Sonuç nedir?",
     "options": [
      "0",
      "10",
      "-1",
      "2",
      "1",
      "-2",
      "?!?"
     ],
     "answer": "0"
    }
   ]
  },
  {
   "id": "easy_8",
   "title": "Parantez Kullanımı",
   "lesson": "Parantez içindeki işlem öncelik kazanır.",
   "questions": [
    {
     "code": "(3 + 2) * 3",
     "prompt": "Parantez içi önce hesaplanır. Sonuç nedir?",
     "options": [
      "17",
      "13",
      "30",
      "15",
      "14",
      "16",
      "?!?"
     ],
     "answer": "15"
    },
    {
     "code": "(7 + 8) * 6",
     "prompt": "Parantez içi önce hesaplanır. Sonuç nedir?",
     "options": [
      "89",
      "91",
      "92",
      "90",
      "180",
      "88",
      "?!?"
     ],
     "answer": "90"
    },
    {
     "code": "(7 + 5) * 4",
     "prompt": "Parantez içi önce hesaplanır. Sonuç nedir?",
     "options": [
      "48",
      "47",
      "50",
      "49",
      "96",
      "46",
      "?!?"
     ],
     "answer": "48"
    }
   ]
  },
  {
   "id": "easy_9",
   "title": "Kendine Ekleme",
   "lesson": "n = n + 5 yazarsan n’nin eski değerine 5 eklenir.",
   "questions": [
    {
     "code": "n = 15\nn = n + 3\nn",
     "prompt": "n'nin son değeri nedir?",
     "options": [
      "20",
      "17",
      "16",
      "19",
      "18",
      "15",
      "?!?"
     ],
     "answer": "18"
    },
    {
     "code": "n = 7\nn = n + 3\nn",
     "prompt": "n'nin son değeri nedir?",
     "options": [
      "12",
      "7",
      "11",
      "8",
      "10",
      "9",
      "?!?"
     ],
     "answer": "10"
    },
    {
     "code": "n = 11\nn = n + 6\nn",
     "prompt": "n'nin son değeri nedir?",
     "options": [
      "11",
      "17",
      "18",
      "15",
      "19",
      "16",
      "?!?"
     ],
     "answer": "17"
    }
   ]
  },
  {
   "id": "easy_10",
   "title": "Kısa İşleçler",
   "lesson": "+=, -=, *= işleçleri değişkeni güncellemenin kısa yoludur.",
   "questions": [
    {
     "code": "n = 2\nn += 6\nn *= 2\nn",
     "prompt": "n'nin son değeri nedir?",
     "options": [
      "17",
      "32",
      "18",
      "16",
      "14",
      "15",
      "?!?"
     ],
     "answer": "16"
    },
    {
     "code": "n = 7\nn += 1\nn *= 3\nn",
     "prompt": "n'nin son değeri nedir?",
     "options": [
      "23",
      "48",
      "25",
      "24",
      "22",
      "26",
      "?!?"
     ],
     "answer": "24"
    },
    {
     "code": "n = 5\nn *= 6\nn += 3\nn",
     "prompt": "n'nin son değeri nedir?",
     "options": [
      "66",
      "35",
      "34",
      "33",
      "32",
      "31",
      "?!?"
     ],
     "answer": "33"
    }
   ]
  },
  {
   "id": "easy_11",
   "title": "Metin Birleştirme",
   "lesson": "+ işleci metinleri uç uca ekler: \"a\" + \"b\" → \"ab\".",
   "questions": [
    {
     "code": "s = 'bilgi'\nt = 'kitap'\ns + t",
     "prompt": "Metinler birleşince ne olur?",
     "options": [
      "'bilgikita'",
      "'ilgikitap'",
      "'patikiglib'",
      "'bilgikitap'",
      "'BILGIKITAP'",
      "'kitapbilgi'",
      "?!?"
     ],
     "answer": "'bilgikitap'"
    },
    {
     "code": "s = 'masa'\nt = 'kodlama'\ns + ' ' + t",
     "prompt": "Metinler birleşince ne olur?",
     "options": [
      "'masa kodlama'",
      "'masa kodlam'",
      "'kodlamamasa'",
      "'MASA KODLAMA'",
      "'asa kodlama'",
      "'amaldok asam'",
      "?!?"
     ],
     "answer": "'masa kodlama'"
    },
    {
     "code": "s = 'kalem'\nt = 'masa'\ns + t",
     "prompt": "Metinler birleşince ne olur?",
     "options": [
      "'asammelak'",
      "'masakalem'",
      "'alemmasa'",
      "'KALEMMASA'",
      "'kalemmasa'",
      "'kalemmas'",
      "?!?"
     ],
     "answer": "'kalemmasa'"
    }
   ]
  },
  {
   "id": "easy_12",
   "title": "Metin Tekrarlama",
   "lesson": "Bir metni sayıyla çarparsan metin tekrarlanır: \"ab\" * 3 → \"ababab\".",
   "questions": [
    {
     "code": "'*' * 5",
     "prompt": "Metin tekrarlanınca ne olur?",
     "options": [
      "'y1'",
      "'***** '",
      "'****'",
      "'******'",
      "'*****'",
      "''",
      "?!?"
     ],
     "answer": "'*****'"
    },
    {
     "code": "'-' * 3",
     "prompt": "Metin tekrarlanınca ne olur?",
     "options": [
      "''",
      "'y1'",
      "'---'",
      "'----'",
      "'--- '",
      "'--'",
      "?!?"
     ],
     "answer": "'---'"
    },
    {
     "code": "'ab' * 4",
     "prompt": "Metin tekrarlanınca ne olur?",
     "options": [
      "'abababa'",
      "'ababababa'",
      "'babababa'",
      "'bababab'",
      "'ABABABAB'",
      "'abababab'",
      "?!?"
     ],
     "answer": "'abababab'"
    }
   ]
  },
  {
   "id": "easy_13",
   "title": "Uzunluk: len()",
   "lesson": "len() bir metindeki karakter ya da listedeki eleman sayısını verir.",
   "questions": [
    {
     "code": "len([2, 4, 5, 8, 1, 6])",
     "prompt": "Listede kaç eleman var?",
     "options": [
      "7",
      "12",
      "8",
      "6",
      "4",
      "5",
      "?!?"
     ],
     "answer": "6"
    },
    {
     "code": "len([9, 4, 7])",
     "prompt": "Listede kaç eleman var?",
     "options": [
      "2",
      "6",
      "4",
      "3",
      "5",
      "1",
      "?!?"
     ],
     "answer": "3"
    },
    {
     "code": "len('masa')",
     "prompt": "len() karakter sayısını verir. Sonuç nedir?",
     "options": [
      "3",
      "2",
      "4",
      "5",
      "8",
      "6",
      "?!?"
     ],
     "answer": "4"
    }
   ]
  },
  {
   "id": "easy_14",
   "title": "Metin İndeksi",
   "lesson": "İndeks 0’dan başlar. s[0] ilk, s[-1] son karakterdir.",
   "questions": [
    {
     "code": "s = 'merhaba'\ns[1]",
     "prompt": "Metin indeksi 0’dan başlar. Sonuç nedir?",
     "options": [
      "'q3'",
      "'z2'",
      "'y1'",
      "''",
      "'e'",
      "'e '",
      "?!?"
     ],
     "answer": "'e'"
    },
    {
     "code": "s = 'bahar'\ns[2]",
     "prompt": "Metin indeksi 0’dan başlar. Sonuç nedir?",
     "options": [
      "'q3'",
      "'y1'",
      "'h'",
      "'z2'",
      "'h '",
      "''",
      "?!?"
     ],
     "answer": "'h'"
    },
    {
     "code": "s = 'okul'\ns[2]",
     "prompt": "Metin indeksi 0’dan başlar. Sonuç nedir?",
     "options": [
      "'y1'",
      "'z2'",
      "'u'",
      "'q3'",
      "'u '",
      "''",
      "?!?"
     ],
     "answer": "'u'"
    }
   ]
  },
  {
   "id": "easy_15",
   "title": "Metin Dilimleme",
   "lesson": "s[a:b] a’dan b’ye kadar (b hariç) olan parçayı verir.",
   "questions": [
    {
     "code": "s = 'dunya'\ns[1:4]",
     "prompt": "Dilimleme: başlangıç dahil, bitiş hariçtir. Sonuç nedir?",
     "options": [
      "'ynu'",
      "'unyu'",
      "'uny'",
      "'ny'",
      "'un'",
      "'UNY'",
      "?!?"
     ],
     "answer": "'uny'"
    },
    {
     "code": "s = 'python'\ns[3:]",
     "prompt": "Dilimleme: başlangıç dahil, bitiş hariçtir. Sonuç nedir?",
     "options": [
      "'hon'",
      "'honh'",
      "'on'",
      "'HON'",
      "'noh'",
      "'ho'",
      "?!?"
     ],
     "answer": "'hon'"
    },
    {
     "code": "s = 'kitap'\ns[:4]",
     "prompt": "Dilimleme: başlangıç dahil, bitiş hariçtir. Sonuç nedir?",
     "options": [
      "'KITA'",
      "'atik'",
      "'kita'",
      "'ita'",
      "'kitak'",
      "'kit'",
      "?!?"
     ],
     "answer": "'kita'"
    }
   ]
  },
  {
   "id": "easy_16",
   "title": "Büyük ve Küçük Harf",
   "lesson": "upper() hepsini büyütür, capitalize() ilk harfi büyütür.",
   "questions": [
    {
     "code": "'elma'.upper()",
     "prompt": "Metin metodunun sonucu nedir?",
     "options": [
      "'ELMA'",
      "'LMA'",
      "''",
      "'ELMAE'",
      "'ELM'",
      "'AMLE'",
      "?!?"
     ],
     "answer": "'ELMA'"
    },
    {
     "code": "'python'.upper()",
     "prompt": "Metin metodunun sonucu nedir?",
     "options": [
      "'PYTHO'",
      "'PYTHON'",
      "'NOHTYP'",
      "'YTHON'",
      "''",
      "'PYTHONP'",
      "?!?"
     ],
     "answer": "'PYTHON'"
    },
    {
     "code": "'bahar'.upper()",
     "prompt": "Metin metodunun sonucu nedir?",
     "options": [
      "'BAHA'",
      "'BAHAR'",
      "'AHAR'",
      "'RAHAB'",
      "'BAHARB'",
      "''",
      "?!?"
     ],
     "answer": "'BAHAR'"
    }
   ]
  },
  {
   "id": "easy_17",
   "title": "print ile Yazdırma",
   "lesson": "print() değeri ekrana yazar; metinlerin tırnağı yazılmaz.",
   "questions": [
    {
     "code": "a = 15\nb = 3\nprint(a + b)",
     "prompt": "Ekrana ne yazdırılır?",
     "options": [
      "None",
      "'y1'",
      "18",
      "81",
      "153",
      "18 ",
      "?!?"
     ],
     "answer": "18"
    },
    {
     "code": "a = 8\nb = 6\nprint(a + b)",
     "prompt": "Ekrana ne yazdırılır?",
     "options": [
      "None",
      "14 ",
      "86",
      "41",
      "14",
      "'y1'",
      "?!?"
     ],
     "answer": "14"
    },
    {
     "code": "a = 15\nb = 6\nprint(a + b)",
     "prompt": "Ekrana ne yazdırılır?",
     "options": [
      "21",
      "12",
      "'y1'",
      "21 ",
      "None",
      "156",
      "?!?"
     ],
     "answer": "21"
    }
   ]
  },
  {
   "id": "easy_18",
   "title": "print ile Çoklu Yazma",
   "lesson": "print(a, b) değerleri boşlukla ayırarak yazar; sep= ile ayırıcıyı değiştirebilirsin.",
   "questions": [
    {
     "code": "print('kitap', 16)",
     "prompt": "print, virgülle ayrılan değerleri boşlukla yazar. Çıktı nedir?",
     "options": [
      "kitap 16",
      "61 patik",
      "None",
      "kitap 16 ",
      "'z2'",
      "'y1'",
      "?!?"
     ],
     "answer": "kitap 16"
    },
    {
     "code": "print('okul', 'ok', sep='-')",
     "prompt": "sep ayırıcıyı belirler. Çıktı nedir?",
     "options": [
      "None",
      "ko-luko",
      "'y1'",
      "'z2'",
      "okul-ok ",
      "okul-ok",
      "?!?"
     ],
     "answer": "okul-ok"
    },
    {
     "code": "print('dunya', 9)",
     "prompt": "print, virgülle ayrılan değerleri boşlukla yazar. Çıktı nedir?",
     "options": [
      "dunya 9 ",
      "'z2'",
      "None",
      "9 aynud",
      "'y1'",
      "dunya 9",
      "?!?"
     ],
     "answer": "dunya 9"
    }
   ]
  },
  {
   "id": "easy_19",
   "title": "Tür Dönüşümü",
   "lesson": "int(), str(), float() ile değerin türünü değiştirebilirsin.",
   "questions": [
    {
     "code": "float(4)",
     "prompt": "float() ondalıklı sayıya çevirir. Sonuç nedir?",
     "options": [
      "5.0",
      "4.0",
      "3.0",
      "5",
      "4",
      "8.0",
      "?!?"
     ],
     "answer": "4.0"
    },
    {
     "code": "str(8) + '5'",
     "prompt": "str() sayıyı metne çevirir. Sonuç nedir?",
     "options": [
      "'858'",
      "'85'",
      "'8'",
      "'58'",
      "13",
      "'5'",
      "?!?"
     ],
     "answer": "'85'"
    },
    {
     "code": "float(9)",
     "prompt": "float() ondalıklı sayıya çevirir. Sonuç nedir?",
     "options": [
      "18.0",
      "9",
      "10.0",
      "9.0",
      "10",
      "8.0",
      "?!?"
     ],
     "answer": "9.0"
    }
   ]
  },
  {
   "id": "easy_20",
   "title": "Yuvarlama ve Mutlak Değer",
   "lesson": "round() yuvarlar, abs() mutlak değer verir, int() ondalığı atar.",
   "questions": [
    {
     "code": "abs(-21)",
     "prompt": "abs() mutlak değeri verir. Sonuç nedir?",
     "options": [
      "23",
      "21",
      "22",
      "-21",
      "19",
      "20",
      "?!?"
     ],
     "answer": "21"
    },
    {
     "code": "round(9.7)",
     "prompt": "round() en yakın tam sayıya yuvarlar. Sonuç nedir?",
     "options": [
      "12",
      "10",
      "8",
      "9",
      "11",
      "20",
      "?!?"
     ],
     "answer": "10"
    },
    {
     "code": "round(2.7)",
     "prompt": "round() en yakın tam sayıya yuvarlar. Sonuç nedir?",
     "options": [
      "4",
      "2",
      "3",
      "1",
      "6",
      "5",
      "?!?"
     ],
     "answer": "3"
    }
   ]
  },
  {
   "id": "easy_21",
   "title": "Liste ve İndeks",
   "lesson": "Liste köşeli parantezle yazılır; indeks 0’dan başlar.",
   "questions": [
    {
     "code": "a = [2, 18, 21, 27]\na[2]",
     "prompt": "Liste indeksi 0’dan başlar. Sonuç nedir?",
     "options": [
      "22",
      "20",
      "21",
      "23",
      "42",
      "19",
      "?!?"
     ],
     "answer": "21"
    },
    {
     "code": "a = [4, 22, 38, 20, 16, 34]\na[2]",
     "prompt": "Liste indeksi 0’dan başlar. Sonuç nedir?",
     "options": [
      "37",
      "39",
      "40",
      "76",
      "38",
      "36",
      "?!?"
     ],
     "answer": "38"
    },
    {
     "code": "a = [9, 22, 5, 19]\na[0]",
     "prompt": "Liste indeksi 0’dan başlar. Sonuç nedir?",
     "options": [
      "8",
      "10",
      "18",
      "11",
      "7",
      "9",
      "?!?"
     ],
     "answer": "9"
    }
   ]
  },
  {
   "id": "easy_22",
   "title": "Negatif İndeks",
   "lesson": "a[-1] listenin son elemanıdır, a[-2] sondan ikincidir.",
   "questions": [
    {
     "code": "a = [15, 34, 12, 33, 27, 8]\na[-2]",
     "prompt": "-1 son elemandır. Sonuç nedir?",
     "options": [
      "26",
      "29",
      "54",
      "25",
      "28",
      "27",
      "?!?"
     ],
     "answer": "27"
    },
    {
     "code": "a = [25, 31, 6, 35, 21]\na[-1]",
     "prompt": "-1 son elemandır. Sonuç nedir?",
     "options": [
      "23",
      "42",
      "22",
      "19",
      "20",
      "21",
      "?!?"
     ],
     "answer": "21"
    },
    {
     "code": "a = [30, 4, 16, 33, 3]\na[-2]",
     "prompt": "-1 son elemandır. Sonuç nedir?",
     "options": [
      "31",
      "34",
      "66",
      "33",
      "35",
      "32",
      "?!?"
     ],
     "answer": "33"
    }
   ]
  },
  {
   "id": "easy_23",
   "title": "Liste Uzunluğu",
   "lesson": "len(liste) eleman sayısını verir; append eleman ekler.",
   "questions": [
    {
     "code": "a = [25, 5, 6, 13]\na.append(99)\nlen(a)",
     "prompt": "append sonrası kaç eleman var?",
     "options": [
      "10",
      "5",
      "4",
      "3",
      "7",
      "6",
      "?!?"
     ],
     "answer": "5"
    },
    {
     "code": "a = [7, 19, 12, 18, 9, 6]\nlen(a)",
     "prompt": "Listede kaç eleman var?",
     "options": [
      "7",
      "5",
      "12",
      "6",
      "8",
      "4",
      "?!?"
     ],
     "answer": "6"
    },
    {
     "code": "a = [25, 27, 11]\nlen(a)",
     "prompt": "Listede kaç eleman var?",
     "options": [
      "6",
      "4",
      "5",
      "1",
      "2",
      "3",
      "?!?"
     ],
     "answer": "3"
    }
   ]
  },
  {
   "id": "easy_24",
   "title": "append ile Ekleme",
   "lesson": "append(x) listenin sonuna x ekler.",
   "questions": [
    {
     "code": "a = [7, 8, 6, 1]\na.append(19)\na",
     "prompt": "append listenin sonuna ekler. a nedir?",
     "options": [
      "[7, 8, 6, 1, 19]",
      "[7, 8, 6, 1]",
      "[]",
      "[19, 7, 8, 6, 1]",
      "[8, 6, 1, 19]",
      "[19, 1, 6, 8, 7]",
      "?!?"
     ],
     "answer": "[7, 8, 6, 1, 19]"
    },
    {
     "code": "a = [8, 9]\na.append(16)\na",
     "prompt": "append listenin sonuna ekler. a nedir?",
     "options": [
      "[8, 9, 16]",
      "[16, 9, 8]",
      "[16, 8, 9]",
      "[]",
      "[8, 9]",
      "[9, 16]",
      "?!?"
     ],
     "answer": "[8, 9, 16]"
    },
    {
     "code": "a = [4, 8, 1, 5]\na.append(16)\na",
     "prompt": "append listenin sonuna ekler. a nedir?",
     "options": [
      "[16, 5, 1, 8, 4]",
      "[16, 4, 8, 1, 5]",
      "[]",
      "[4, 8, 1, 5, 16]",
      "[4, 8, 1, 5]",
      "[8, 1, 5, 16]",
      "?!?"
     ],
     "answer": "[4, 8, 1, 5, 16]"
    }
   ]
  },
  {
   "id": "easy_25",
   "title": "Liste Elemanını Değiştirme",
   "lesson": "a[i] = x ile i. elemanı değiştirebilirsin.",
   "questions": [
    {
     "code": "a = [14, 4, 29, 28]\na[0] = 35\na",
     "prompt": "Liste elemanı değiştirilebilir. a nedir?",
     "options": [
      "[28, 29, 4, 35]",
      "[35, 4, 29]",
      "[4, 29, 28]",
      "[35, 4, 29, 28]",
      "[]",
      "[35, 4, 29, 28, 35]",
      "?!?"
     ],
     "answer": "[35, 4, 29, 28]"
    },
    {
     "code": "a = [13, 4, 3, 16]\na[3] = 38\na",
     "prompt": "Liste elemanı değiştirilebilir. a nedir?",
     "options": [
      "[4, 3, 38]",
      "[13, 4, 3]",
      "[13, 4, 3, 38, 13]",
      "[38, 3, 4, 13]",
      "[]",
      "[13, 4, 3, 38]",
      "?!?"
     ],
     "answer": "[13, 4, 3, 38]"
    },
    {
     "code": "a = [14, 18, 16, 21]\na[2] = 55\na",
     "prompt": "Liste elemanı değiştirilebilir. a nedir?",
     "options": [
      "[]",
      "[14, 18, 55]",
      "[14, 18, 55, 21]",
      "[14, 18, 55, 21, 14]",
      "[18, 55, 21]",
      "[21, 55, 18, 14]",
      "?!?"
     ],
     "answer": "[14, 18, 55, 21]"
    }
   ]
  },
  {
   "id": "easy_26",
   "title": "Eşitlik Karşılaştırma",
   "lesson": "== eşit mi, != eşit değil mi diye bakar; sonuç True ya da False olur.",
   "questions": [
    {
     "code": "3 == 4",
     "prompt": "Sonuç True mu False mu?",
     "options": [
      "True",
      "None",
      "'True'",
      "False",
      "0",
      "1",
      "?!?"
     ],
     "answer": "False"
    },
    {
     "code": "11 != 12",
     "prompt": "Sonuç True mu False mu?",
     "options": [
      "1",
      "0",
      "True",
      "'True'",
      "False",
      "None",
      "?!?"
     ],
     "answer": "True"
    },
    {
     "code": "20 == 40",
     "prompt": "Sonuç True mu False mu?",
     "options": [
      "False",
      "None",
      "1",
      "0",
      "True",
      "'True'",
      "?!?"
     ],
     "answer": "False"
    }
   ]
  },
  {
   "id": "easy_27",
   "title": "Büyük / Küçük Karşılaştırma",
   "lesson": "<, >, <=, >= ile sayıları kıyaslarsın.",
   "questions": [
    {
     "code": "19 >= 14",
     "prompt": "Karşılaştırmanın sonucu nedir?",
     "options": [
      "'True'",
      "None",
      "1",
      "True",
      "0",
      "False",
      "?!?"
     ],
     "answer": "True"
    },
    {
     "code": "11 > 5",
     "prompt": "Karşılaştırmanın sonucu nedir?",
     "options": [
      "0",
      "False",
      "True",
      "None",
      "'True'",
      "1",
      "?!?"
     ],
     "answer": "True"
    },
    {
     "code": "18 > 17",
     "prompt": "Karşılaştırmanın sonucu nedir?",
     "options": [
      "'True'",
      "1",
      "0",
      "True",
      "None",
      "False",
      "?!?"
     ],
     "answer": "True"
    }
   ]
  },
  {
   "id": "easy_28",
   "title": "and Mantıksal İşleci",
   "lesson": "and: iki koşul da doğruysa sonuç True olur.",
   "questions": [
    {
     "code": "1 > 7 and 1 < 6",
     "prompt": "and: iki koşul da doğru olmalı. Sonuç nedir?",
     "options": [
      "False",
      "1",
      "'True'",
      "True",
      "None",
      "0",
      "?!?"
     ],
     "answer": "False"
    },
    {
     "code": "3 > 1 and 3 < 7",
     "prompt": "and: iki koşul da doğru olmalı. Sonuç nedir?",
     "options": [
      "True",
      "0",
      "'True'",
      "False",
      "1",
      "None",
      "?!?"
     ],
     "answer": "True"
    },
    {
     "code": "6 > 6 and 7 < 5",
     "prompt": "and: iki koşul da doğru olmalı. Sonuç nedir?",
     "options": [
      "True",
      "None",
      "0",
      "'True'",
      "1",
      "False",
      "?!?"
     ],
     "answer": "False"
    }
   ]
  },
  {
   "id": "easy_29",
   "title": "or Mantıksal İşleci",
   "lesson": "or: koşullardan biri doğruysa sonuç True olur.",
   "questions": [
    {
     "code": "8 == 2 or 6 > 3",
     "prompt": "or: biri doğruysa sonuç doğru olur. Sonuç nedir?",
     "options": [
      "0",
      "True",
      "1",
      "False",
      "None",
      "'True'",
      "?!?"
     ],
     "answer": "True"
    },
    {
     "code": "8 == 3 or 6 > 8",
     "prompt": "or: biri doğruysa sonuç doğru olur. Sonuç nedir?",
     "options": [
      "False",
      "None",
      "True",
      "1",
      "0",
      "'True'",
      "?!?"
     ],
     "answer": "False"
    },
    {
     "code": "2 == 5 or 7 > 1",
     "prompt": "or: biri doğruysa sonuç doğru olur. Sonuç nedir?",
     "options": [
      "0",
      "False",
      "1",
      "True",
      "'True'",
      "None",
      "?!?"
     ],
     "answer": "True"
    }
   ]
  },
  {
   "id": "easy_30",
   "title": "not Mantıksal İşleci",
   "lesson": "not, True/False değerini tersine çevirir.",
   "questions": [
    {
     "code": "not False",
     "prompt": "not sonucu tersine çevirir. Sonuç nedir?",
     "options": [
      "True",
      "1",
      "0",
      "None",
      "False",
      "'True'",
      "?!?"
     ],
     "answer": "True"
    },
    {
     "code": "not True",
     "prompt": "not sonucu tersine çevirir. Sonuç nedir?",
     "options": [
      "False",
      "1",
      "None",
      "0",
      "'True'",
      "True",
      "?!?"
     ],
     "answer": "False"
    },
    {
     "code": "not (3 > 8)",
     "prompt": "not sonucu tersine çevirir. Sonuç nedir?",
     "options": [
      "1",
      "0",
      "'True'",
      "False",
      "None",
      "True",
      "?!?"
     ],
     "answer": "True"
    }
   ]
  },
  {
   "id": "easy_31",
   "title": "in İşleci",
   "lesson": "in, bir değerin metin ya da liste içinde olup olmadığını söyler.",
   "questions": [
    {
     "code": "'z' not in 'elma'",
     "prompt": "not in, içinde yok mu diye bakar. Sonuç nedir?",
     "options": [
      "1",
      "False",
      "None",
      "0",
      "'True'",
      "True",
      "?!?"
     ],
     "answer": "True"
    },
    {
     "code": "11 in [8, 9, 7, 1]",
     "prompt": "Listede var mı? Sonuç nedir?",
     "options": [
      "None",
      "False",
      "1",
      "'True'",
      "True",
      "0",
      "?!?"
     ],
     "answer": "False"
    },
    {
     "code": "'a' in 'elma'",
     "prompt": "in, içinde var mı diye bakar. Sonuç nedir?",
     "options": [
      "False",
      "0",
      "'True'",
      "1",
      "None",
      "True",
      "?!?"
     ],
     "answer": "True"
    }
   ]
  },
  {
   "id": "easy_32",
   "title": "if Koşulu",
   "lesson": "if koşul doğruysa içindeki girintili kod çalışır.",
   "questions": [
    {
     "code": "x = 4\nif x > 3:\n    x = x + 4\nx",
     "prompt": "x'in son değeri nedir?",
     "options": [
      "6",
      "16",
      "7",
      "8",
      "10",
      "9",
      "?!?"
     ],
     "answer": "8"
    },
    {
     "code": "x = 6\nif x > 5:\n    x = x + 2\nx",
     "prompt": "x'in son değeri nedir?",
     "options": [
      "16",
      "9",
      "6",
      "7",
      "8",
      "10",
      "?!?"
     ],
     "answer": "8"
    },
    {
     "code": "x = 8\nif x > 9:\n    x = x + 4\nx",
     "prompt": "x'in son değeri nedir?",
     "options": [
      "8",
      "10",
      "16",
      "7",
      "9",
      "6",
      "?!?"
     ],
     "answer": "8"
    }
   ]
  },
  {
   "id": "easy_33",
   "title": "if / else",
   "lesson": "Koşul doğruysa if bloğu, değilse else bloğu çalışır.",
   "questions": [
    {
     "code": "n = 9\nif n > 4:\n    n = 15\nelse:\n    n = 25\nn",
     "prompt": "n'nin son değeri nedir?",
     "options": [
      "13",
      "15",
      "16",
      "25",
      "17",
      "14",
      "?!?"
     ],
     "answer": "15"
    },
    {
     "code": "n = 2\nif n > 8:\n    n = 15\nelse:\n    n = 28\nn",
     "prompt": "n'nin son değeri nedir?",
     "options": [
      "29",
      "26",
      "28",
      "30",
      "15",
      "27",
      "?!?"
     ],
     "answer": "28"
    },
    {
     "code": "n = 4\nif n > 4:\n    n = 12\nelse:\n    n = 29\nn",
     "prompt": "n'nin son değeri nedir?",
     "options": [
      "27",
      "30",
      "12",
      "29",
      "28",
      "31",
      "?!?"
     ],
     "answer": "29"
    }
   ]
  },
  {
   "id": "easy_34",
   "title": "min, max, sum",
   "lesson": "max en büyüğü, min en küçüğü, sum toplamı verir.",
   "questions": [
    {
     "code": "min([27, 38, 9, 14, 1])",
     "prompt": "min en küçüğü verir. Sonuç nedir?",
     "options": [
      "1",
      "0",
      "11",
      "3",
      "2",
      "-1",
      "?!?"
     ],
     "answer": "1"
    },
    {
     "code": "max([33, 40, 13, 17, 25])",
     "prompt": "max en büyüğü verir. Sonuç nedir?",
     "options": [
      "80",
      "38",
      "41",
      "40",
      "42",
      "39",
      "?!?"
     ],
     "answer": "40"
    },
    {
     "code": "max(49, 47, 19)",
     "prompt": "En büyük değer hangisi?",
     "options": [
      "48",
      "51",
      "47",
      "98",
      "49",
      "50",
      "?!?"
     ],
     "answer": "49"
    }
   ]
  }
 ],
 "medium": [
  {
   "id": "mid_1",
   "title": "elif Zinciri",
   "lesson": "İlk doğru koşulun bloğu çalışır, diğer dallar atlanır.",
   "questions": [
    {
     "code": "p = 94\nif p >= 90:\n    n = 'A'\nelif p >= 70:\n    n = 'B'\nelse:\n    n = 'C'\nn",
     "prompt": "İlk doğru koşulun dalı çalışır. n nedir?",
     "options": [
      "'A'",
      "'B'",
      "''",
      "'A '",
      "'C'",
      "'y1'",
      "?!?"
     ],
     "answer": "'A'"
    },
    {
     "code": "p = 87\nif p >= 90:\n    n = 'A'\nelif p >= 60:\n    n = 'B'\nelse:\n    n = 'C'\nn",
     "prompt": "İlk doğru koşulun dalı çalışır. n nedir?",
     "options": [
      "'B '",
      "'A'",
      "'y1'",
      "'B'",
      "'C'",
      "''",
      "?!?"
     ],
     "answer": "'B'"
    },
    {
     "code": "p = 35\nif p >= 90:\n    n = 'A'\nelif p >= 70:\n    n = 'B'\nelse:\n    n = 'C'\nn",
     "prompt": "İlk doğru koşulun dalı çalışır. n nedir?",
     "options": [
      "'B'",
      "'C '",
      "'C'",
      "''",
      "'y1'",
      "'A'",
      "?!?"
     ],
     "answer": "'C'"
    }
   ]
  },
  {
   "id": "mid_2",
   "title": "İç İçe Koşul",
   "lesson": "Bir if bloğunun içinde başka bir if olabilir.",
   "questions": [
    {
     "code": "x = 4\ny = 4\nif x > 3:\n    if y > 3:\n        r = 1\n    else:\n        r = 2\nelse:\n    r = 3\nr",
     "prompt": "r'nin son değeri nedir?",
     "options": [
      "11",
      "-1",
      "2",
      "3",
      "1",
      "0",
      "?!?"
     ],
     "answer": "1"
    },
    {
     "code": "x = 2\ny = 2\nif x > 9:\n    if y > 3:\n        r = 1\n    else:\n        r = 2\nelse:\n    r = 3\nr",
     "prompt": "r'nin son değeri nedir?",
     "options": [
      "1",
      "2",
      "4",
      "3",
      "6",
      "5",
      "?!?"
     ],
     "answer": "3"
    },
    {
     "code": "x = 4\ny = 9\nif x > 8:\n    if y > 1:\n        r = 1\n    else:\n        r = 2\nelse:\n    r = 3\nr",
     "prompt": "r'nin son değeri nedir?",
     "options": [
      "4",
      "3",
      "1",
      "2",
      "6",
      "5",
      "?!?"
     ],
     "answer": "3"
    }
   ]
  },
  {
   "id": "mid_3",
   "title": "Koşullarda and",
   "lesson": "if içinde and kullanarak birden çok şartı birlikte kontrol edebilirsin.",
   "questions": [
    {
     "code": "x = 5\ny = 4\nif x > 3 and y < 6:\n    s = 'evet'\nelse:\n    s = 'hayir'\ns",
     "prompt": "s nedir?",
     "options": [
      "'eve'",
      "'hayir'",
      "'EVET'",
      "'teve'",
      "'evet'",
      "'vet'",
      "?!?"
     ],
     "answer": "'evet'"
    },
    {
     "code": "x = 12\ny = 5\nif x > 9 and y < 6:\n    s = 'evet'\nelse:\n    s = 'hayir'\ns",
     "prompt": "s nedir?",
     "options": [
      "'eve'",
      "'evet'",
      "'hayir'",
      "'EVET'",
      "'teve'",
      "'vet'",
      "?!?"
     ],
     "answer": "'evet'"
    },
    {
     "code": "x = 6\ny = 12\nif x > 2 and y < 9:\n    s = 'evet'\nelse:\n    s = 'hayir'\ns",
     "prompt": "s nedir?",
     "options": [
      "'HAYIR'",
      "'evet'",
      "'ayir'",
      "'riyah'",
      "'hayir'",
      "'hayi'",
      "?!?"
     ],
     "answer": "'hayir'"
    }
   ]
  },
  {
   "id": "mid_4",
   "title": "for ve range(n)",
   "lesson": "range(n) 0’dan n-1’e kadar sayar; for her sayı için bloğu çalıştırır.",
   "questions": [
    {
     "code": "t = 0\nfor i in range(4):\n    t = t + i\nt",
     "prompt": "range(n) 0’dan n-1’e kadar sayar. t nedir?",
     "options": [
      "4",
      "5",
      "7",
      "6",
      "10",
      "8",
      "?!?"
     ],
     "answer": "6"
    },
    {
     "code": "t = 0\nfor i in range(6):\n    t = t + i\nt",
     "prompt": "range(n) 0’dan n-1’e kadar sayar. t nedir?",
     "options": [
      "14",
      "21",
      "15",
      "16",
      "17",
      "13",
      "?!?"
     ],
     "answer": "15"
    },
    {
     "code": "t = 0\nfor i in range(8):\n    t = t + i\nt",
     "prompt": "range(n) 0’dan n-1’e kadar sayar. t nedir?",
     "options": [
      "28",
      "36",
      "29",
      "26",
      "30",
      "27",
      "?!?"
     ],
     "answer": "28"
    }
   ]
  },
  {
   "id": "mid_5",
   "title": "range(başla, bitir)",
   "lesson": "range(a, b): a dahil, b hariç sayılar üretir.",
   "questions": [
    {
     "code": "t = 0\nfor i in range(5, 8):\n    t += i\nt",
     "prompt": "range(a, b): a dahil, b hariç. t nedir?",
     "options": [
      "16",
      "36",
      "18",
      "20",
      "19",
      "17",
      "?!?"
     ],
     "answer": "18"
    },
    {
     "code": "t = 0\nfor i in range(2, 6):\n    t += i\nt",
     "prompt": "range(a, b): a dahil, b hariç. t nedir?",
     "options": [
      "14",
      "12",
      "15",
      "16",
      "13",
      "28",
      "?!?"
     ],
     "answer": "14"
    },
    {
     "code": "t = 0\nfor i in range(4, 6):\n    t += i\nt",
     "prompt": "range(a, b): a dahil, b hariç. t nedir?",
     "options": [
      "7",
      "10",
      "9",
      "18",
      "8",
      "11",
      "?!?"
     ],
     "answer": "9"
    }
   ]
  },
  {
   "id": "mid_6",
   "title": "range ile Adım",
   "lesson": "range(a, b, s) s’er s’er sayar.",
   "questions": [
    {
     "code": "list(range(2, 11, 4))",
     "prompt": "Üçüncü sayı adım miktarıdır. Sonuç nedir?",
     "options": [
      "[2, 6]",
      "[2, 6, 10, 2]",
      "[10, 6, 2]",
      "[2, 6, 10]",
      "[6, 10]",
      "[]",
      "?!?"
     ],
     "answer": "[2, 6, 10]"
    },
    {
     "code": "list(range(2, 19, 4))",
     "prompt": "Üçüncü sayı adım miktarıdır. Sonuç nedir?",
     "options": [
      "[6, 10, 14, 18]",
      "[2, 6, 10, 14, 18]",
      "[18, 14, 10, 6, 2]",
      "[]",
      "[2, 6, 10, 14]",
      "[2, 6, 10, 14, 18, 2]",
      "?!?"
     ],
     "answer": "[2, 6, 10, 14, 18]"
    },
    {
     "code": "t = 0\nfor i in range(2, 11, 2):\n    t += 1\nt",
     "prompt": "Döngü kaç kez çalışır?",
     "options": [
      "5",
      "7",
      "6",
      "3",
      "10",
      "4",
      "?!?"
     ],
     "answer": "5"
    }
   ]
  },
  {
   "id": "mid_7",
   "title": "Liste Üzerinde for",
   "lesson": "for x in liste: her eleman için bloğu çalıştırır.",
   "questions": [
    {
     "code": "t = 0\nfor x in [6, 4, 7, 3, 2]:\n    t += x\nt",
     "prompt": "t nedir?",
     "options": [
      "44",
      "20",
      "23",
      "24",
      "22",
      "21",
      "?!?"
     ],
     "answer": "22"
    },
    {
     "code": "t = 0\nfor x in [3, 7, 6, 2]:\n    t += x\nt",
     "prompt": "t nedir?",
     "options": [
      "18",
      "20",
      "16",
      "36",
      "17",
      "19",
      "?!?"
     ],
     "answer": "18"
    },
    {
     "code": "t = 0\nfor x in [7, 5, 3]:\n    t += x\nt",
     "prompt": "t nedir?",
     "options": [
      "14",
      "30",
      "15",
      "13",
      "17",
      "16",
      "?!?"
     ],
     "answer": "15"
    }
   ]
  },
  {
   "id": "mid_8",
   "title": "while Döngüsü",
   "lesson": "while koşul doğru olduğu sürece bloğu tekrarlar.",
   "questions": [
    {
     "code": "i = 0\nwhile i < 13:\n    i += 4\ni",
     "prompt": "Koşul bozulana kadar döngü sürer. i nedir?",
     "options": [
      "17",
      "14",
      "15",
      "16",
      "18",
      "32",
      "?!?"
     ],
     "answer": "16"
    },
    {
     "code": "i = 0\nwhile i < 12:\n    i += 4\ni",
     "prompt": "Koşul bozulana kadar döngü sürer. i nedir?",
     "options": [
      "14",
      "24",
      "10",
      "12",
      "11",
      "13",
      "?!?"
     ],
     "answer": "12"
    },
    {
     "code": "i = 0\nwhile i < 17:\n    i += 2\ni",
     "prompt": "Koşul bozulana kadar döngü sürer. i nedir?",
     "options": [
      "20",
      "19",
      "17",
      "18",
      "36",
      "16",
      "?!?"
     ],
     "answer": "18"
    }
   ]
  },
  {
   "id": "mid_9",
   "title": "while ile Toplam",
   "lesson": "while ile sayaç artırarak toplam biriktirebilirsin.",
   "questions": [
    {
     "code": "t = 0\ni = 1\nwhile i <= 6:\n    t += i\n    i += 1\nt",
     "prompt": "t nedir?",
     "options": [
      "22",
      "23",
      "19",
      "20",
      "21",
      "15",
      "?!?"
     ],
     "answer": "21"
    },
    {
     "code": "t = 0\ni = 1\nwhile i <= 5:\n    t += i\n    i += 1\nt",
     "prompt": "t nedir?",
     "options": [
      "17",
      "15",
      "14",
      "16",
      "10",
      "13",
      "?!?"
     ],
     "answer": "15"
    },
    {
     "code": "t = 0\ni = 1\nwhile i <= 7:\n    t += i\n    i += 1\nt",
     "prompt": "t nedir?",
     "options": [
      "29",
      "27",
      "30",
      "28",
      "21",
      "26",
      "?!?"
     ],
     "answer": "28"
    }
   ]
  },
  {
   "id": "mid_10",
   "title": "break",
   "lesson": "break döngüyü hemen bitirir.",
   "questions": [
    {
     "code": "for i in range(10):\n    if i == 3:\n        break\ni",
     "prompt": "break döngüyü hemen bitirir. i nedir?",
     "options": [
      "4",
      "5",
      "1",
      "3",
      "2",
      "6",
      "?!?"
     ],
     "answer": "3"
    },
    {
     "code": "t = 0\nfor i in range(10):\n    if i == 6:\n        break\n    t += i\nt",
     "prompt": "break görülünce döngü biter. t nedir?",
     "options": [
      "17",
      "30",
      "13",
      "14",
      "16",
      "15",
      "?!?"
     ],
     "answer": "15"
    },
    {
     "code": "t = 0\nfor i in range(10):\n    if i == 4:\n        break\n    t += i\nt",
     "prompt": "break görülünce döngü biter. t nedir?",
     "options": [
      "12",
      "6",
      "8",
      "5",
      "7",
      "4",
      "?!?"
     ],
     "answer": "6"
    }
   ]
  },
  {
   "id": "mid_11",
   "title": "continue",
   "lesson": "continue o turu atlayıp bir sonrakine geçer.",
   "questions": [
    {
     "code": "t = 0\nfor i in range(7):\n    if i % 2 == 0:\n        continue\n    t += i\nt",
     "prompt": "continue o turu atlar. t nedir?",
     "options": [
      "7",
      "18",
      "8",
      "11",
      "9",
      "10",
      "?!?"
     ],
     "answer": "9"
    },
    {
     "code": "t = 0\nfor i in range(9):\n    if i % 2 == 0:\n        continue\n    t += i\nt",
     "prompt": "continue o turu atlar. t nedir?",
     "options": [
      "15",
      "17",
      "14",
      "16",
      "32",
      "18",
      "?!?"
     ],
     "answer": "16"
    },
    {
     "code": "t = 0\nfor i in range(5):\n    if i % 2 == 0:\n        continue\n    t += i\nt",
     "prompt": "continue o turu atlar. t nedir?",
     "options": [
      "5",
      "6",
      "8",
      "2",
      "4",
      "3",
      "?!?"
     ],
     "answer": "4"
    }
   ]
  },
  {
   "id": "mid_12",
   "title": "Döngüde Koşul ve Sayaç",
   "lesson": "Döngü içinde if ile koşullu sayma yapılabilir.",
   "questions": [
    {
     "code": "c = 0\nfor x in [12, 29, 15, 4, 24]:\n    if x % 2 == 0:\n        c += 1\nc",
     "prompt": "Çift sayıları sayar. c nedir?",
     "options": [
      "6",
      "5",
      "4",
      "3",
      "2",
      "1",
      "?!?"
     ],
     "answer": "3"
    },
    {
     "code": "c = 0\nfor x in [23, 22, 21, 15, 26, 14, 10]:\n    if x % 2 == 0:\n        c += 1\nc",
     "prompt": "Çift sayıları sayar. c nedir?",
     "options": [
      "3",
      "5",
      "2",
      "6",
      "8",
      "4",
      "?!?"
     ],
     "answer": "4"
    },
    {
     "code": "c = 0\nfor x in [3, 26, 23, 6, 11]:\n    if x % 2 == 0:\n        c += 1\nc",
     "prompt": "Çift sayıları sayar. c nedir?",
     "options": [
      "3",
      "12",
      "1",
      "2",
      "4",
      "0",
      "?!?"
     ],
     "answer": "2"
    }
   ]
  },
  {
   "id": "mid_13",
   "title": "Döngü ile Metin Oluşturma",
   "lesson": "Döngüyle bir metni karakter karakter inşa edebilirsin.",
   "questions": [
    {
     "code": "s = ''\nfor c in 'abc':\n    s = s + c * 2\ns",
     "prompt": "s nedir?",
     "options": [
      "'aabbcca'",
      "'aabbc'",
      "'abbcc'",
      "'AABBCC'",
      "'ccbbaa'",
      "'aabbcc'",
      "?!?"
     ],
     "answer": "'aabbcc'"
    },
    {
     "code": "s = ''\nfor c in 'ali':\n    s = c + s\ns",
     "prompt": "s nedir?",
     "options": [
      "'ILA'",
      "'ali'",
      "'ilai'",
      "'la'",
      "'il'",
      "'ila'",
      "?!?"
     ],
     "answer": "'ila'"
    },
    {
     "code": "s = ''\nfor c in 'abc':\n    s = c + s\ns",
     "prompt": "s nedir?",
     "options": [
      "'cb'",
      "'CBA'",
      "'abc'",
      "'cbac'",
      "'ba'",
      "'cba'",
      "?!?"
     ],
     "answer": "'cba'"
    }
   ]
  },
  {
   "id": "mid_14",
   "title": "Fonksiyon Tanımlama",
   "lesson": "def ile fonksiyon yazılır; return sonucu geri verir.",
   "questions": [
    {
     "code": "def f(a, b):\n    return a * b\n\nf(6, 7)",
     "prompt": "Fonksiyon çağrısının sonucu nedir?",
     "options": [
      "42",
      "41",
      "43",
      "44",
      "40",
      "84",
      "?!?"
     ],
     "answer": "42"
    },
    {
     "code": "def f(a, b):\n    return a * b\n\nf(2, 8)",
     "prompt": "Fonksiyon çağrısının sonucu nedir?",
     "options": [
      "15",
      "32",
      "17",
      "14",
      "16",
      "18",
      "?!?"
     ],
     "answer": "16"
    },
    {
     "code": "def f(a, b):\n    return a * b\n\nf(4, 7)",
     "prompt": "Fonksiyon çağrısının sonucu nedir?",
     "options": [
      "28",
      "29",
      "30",
      "26",
      "56",
      "27",
      "?!?"
     ],
     "answer": "28"
    }
   ]
  },
  {
   "id": "mid_15",
   "title": "Parametreli Fonksiyon",
   "lesson": "Fonksiyon aldığı değerle hesap yapıp sonucu döndürür.",
   "questions": [
    {
     "code": "def kare(n):\n    return n * n\n\nkare(5) + 5",
     "prompt": "Sonuç nedir?",
     "options": [
      "28",
      "31",
      "32",
      "30",
      "25",
      "29",
      "?!?"
     ],
     "answer": "30"
    },
    {
     "code": "def kare(n):\n    return n * n\n\nkare(8) + 3",
     "prompt": "Sonuç nedir?",
     "options": [
      "67",
      "65",
      "64",
      "66",
      "68",
      "69",
      "?!?"
     ],
     "answer": "67"
    },
    {
     "code": "def kare(n):\n    return n * n\n\nkare(9) + 2",
     "prompt": "Sonuç nedir?",
     "options": [
      "83",
      "81",
      "85",
      "166",
      "84",
      "82",
      "?!?"
     ],
     "answer": "83"
    }
   ]
  },
  {
   "id": "mid_16",
   "title": "Varsayılan Parametre",
   "lesson": "Parametreye varsayılan değer verirsen çağrıda yazmak zorunda kalmazsın.",
   "questions": [
    {
     "code": "def f(a, b=2):\n    return a * b\n\nf(5)",
     "prompt": "Verilmeyen parametre varsayılanı kullanır. Sonuç nedir?",
     "options": [
      "9",
      "20",
      "12",
      "11",
      "8",
      "10",
      "?!?"
     ],
     "answer": "10"
    },
    {
     "code": "def f(a, b=2):\n    return a * b\n\nf(9)",
     "prompt": "Verilmeyen parametre varsayılanı kullanır. Sonuç nedir?",
     "options": [
      "17",
      "36",
      "18",
      "16",
      "19",
      "20",
      "?!?"
     ],
     "answer": "18"
    },
    {
     "code": "def f(a, b=2):\n    return a * b\n\nf(4)",
     "prompt": "Verilmeyen parametre varsayılanı kullanır. Sonuç nedir?",
     "options": [
      "8",
      "10",
      "9",
      "16",
      "6",
      "7",
      "?!?"
     ],
     "answer": "8"
    }
   ]
  },
  {
   "id": "mid_17",
   "title": "Fonksiyon ve Koşul",
   "lesson": "Fonksiyon içinde if ile farklı sonuçlar döndürebilirsin.",
   "questions": [
    {
     "code": "def sec(a, b):\n    if a > b:\n        return a\n    return b\n\nsec(10, 7)",
     "prompt": "Sonuç nedir?",
     "options": [
      "20",
      "10",
      "12",
      "11",
      "8",
      "9",
      "?!?"
     ],
     "answer": "10"
    },
    {
     "code": "def sec(a, b):\n    if a > b:\n        return a\n    return b\n\nsec(11, 14)",
     "prompt": "Sonuç nedir?",
     "options": [
      "16",
      "15",
      "28",
      "12",
      "14",
      "13",
      "?!?"
     ],
     "answer": "14"
    },
    {
     "code": "def sec(a, b):\n    if a > b:\n        return a\n    return b\n\nsec(15, 14)",
     "prompt": "Sonuç nedir?",
     "options": [
      "15",
      "13",
      "16",
      "30",
      "17",
      "14",
      "?!?"
     ],
     "answer": "15"
    }
   ]
  },
  {
   "id": "mid_18",
   "title": "Fonksiyon Sonuçlarını Kullanma",
   "lesson": "Fonksiyon sonucu bir ifadenin parçası olabilir.",
   "questions": [
    {
     "code": "def f(x):\n    return x * 2\n\nf(6) + f(5)",
     "prompt": "Sonuç nedir?",
     "options": [
      "20",
      "21",
      "22",
      "23",
      "44",
      "24",
      "?!?"
     ],
     "answer": "22"
    },
    {
     "code": "def f(x):\n    return x * 2\n\nf(2) + f(8)",
     "prompt": "Sonuç nedir?",
     "options": [
      "19",
      "21",
      "18",
      "40",
      "22",
      "20",
      "?!?"
     ],
     "answer": "20"
    },
    {
     "code": "def f(x):\n    return x * 2\n\nf(2) + f(9)",
     "prompt": "Sonuç nedir?",
     "options": [
      "20",
      "24",
      "44",
      "22",
      "21",
      "23",
      "?!?"
     ],
     "answer": "22"
    }
   ]
  },
  {
   "id": "mid_19",
   "title": "Liste Dilimleme",
   "lesson": "liste[a:b] a’dan b’ye (b hariç) alt liste verir.",
   "questions": [
    {
     "code": "a = [33, 18, 25, 29, 27, 44]\na[:2]",
     "prompt": "İlk iki eleman. Sonuç nedir?",
     "options": [
      "[33, 18, 33]",
      "[33, 18]",
      "[18]",
      "[33]",
      "[]",
      "[18, 33]",
      "?!?"
     ],
     "answer": "[33, 18]"
    },
    {
     "code": "a = [31, 23, 4, 7, 47, 37]\na[:2]",
     "prompt": "İlk iki eleman. Sonuç nedir?",
     "options": [
      "[31]",
      "[23]",
      "[]",
      "[23, 31]",
      "[31, 23]",
      "[31, 23, 31]",
      "?!?"
     ],
     "answer": "[31, 23]"
    },
    {
     "code": "a = [19, 14, 43, 45, 50, 2]\na[-2:]",
     "prompt": "Son iki eleman. Sonuç nedir?",
     "options": [
      "[2, 50]",
      "[]",
      "[2]",
      "[50, 2, 50]",
      "[50, 2]",
      "[50]",
      "?!?"
     ],
     "answer": "[50, 2]"
    }
   ]
  },
  {
   "id": "mid_20",
   "title": "Liste Metotları",
   "lesson": "append ekler, pop sondakini siler, insert araya ekler.",
   "questions": [
    {
     "code": "a = [2, 8, 5]\na.append(19)\na.pop()\na.insert(0, 29)\na",
     "prompt": "pop son elemanı siler, insert araya ekler. a nedir?",
     "options": [
      "[29, 2, 8, 5, 29]",
      "[2, 8, 5]",
      "[]",
      "[29, 2, 8, 5]",
      "[29, 2, 8]",
      "[5, 8, 2, 29]",
      "?!?"
     ],
     "answer": "[29, 2, 8, 5]"
    },
    {
     "code": "a = [8, 7, 6]\na.append(15)\na.pop()\na.insert(0, 21)\na",
     "prompt": "pop son elemanı siler, insert araya ekler. a nedir?",
     "options": [
      "[21, 8, 7]",
      "[6, 7, 8, 21]",
      "[]",
      "[21, 8, 7, 6, 21]",
      "[8, 7, 6]",
      "[21, 8, 7, 6]",
      "?!?"
     ],
     "answer": "[21, 8, 7, 6]"
    },
    {
     "code": "a = [5, 8, 7]\na.append(16)\na.pop()\na.insert(0, 30)\na",
     "prompt": "pop son elemanı siler, insert araya ekler. a nedir?",
     "options": [
      "[30, 5, 8, 7, 30]",
      "[7, 8, 5, 30]",
      "[5, 8, 7]",
      "[]",
      "[30, 5, 8]",
      "[30, 5, 8, 7]",
      "?!?"
     ],
     "answer": "[30, 5, 8, 7]"
    }
   ]
  },
  {
   "id": "mid_21",
   "title": "Sıralama",
   "lesson": "sorted() yeni sıralı liste verir; sort() listeyi yerinde sıralar.",
   "questions": [
    {
     "code": "sorted([25, 17, 10, 29])",
     "prompt": "sorted küçükten büyüğe sıralar. Sonuç nedir?",
     "options": [
      "[10, 17, 25, 29]",
      "[10, 17, 25]",
      "[10, 17, 25, 29, 10]",
      "[29, 25, 17, 10]",
      "[]",
      "[17, 25, 29]",
      "?!?"
     ],
     "answer": "[10, 17, 25, 29]"
    },
    {
     "code": "sorted([18, 15, 23, 6])",
     "prompt": "sorted küçükten büyüğe sıralar. Sonuç nedir?",
     "options": [
      "[6, 15, 18, 23, 6]",
      "[15, 18, 23]",
      "[6, 15, 18, 23]",
      "[]",
      "[23, 18, 15, 6]",
      "[6, 15, 18]",
      "?!?"
     ],
     "answer": "[6, 15, 18, 23]"
    },
    {
     "code": "a = [21, 14, 17, 15]\na.sort()\na[0]",
     "prompt": "sort sonrası ilk eleman nedir?",
     "options": [
      "15",
      "13",
      "14",
      "16",
      "28",
      "12",
      "?!?"
     ],
     "answer": "14"
    }
   ]
  },
  {
   "id": "mid_22",
   "title": "En Büyüğü Bulma",
   "lesson": "Döngüyle her elemanı mevcut en büyükle kıyaslayarak maksimumu bulabilirsin.",
   "questions": [
    {
     "code": "a = [42, 12, 2, 46, 23]\nm = a[0]\nfor x in a:\n    if x > m:\n        m = x\nm",
     "prompt": "m neyi bulur? Değeri nedir?",
     "options": [
      "48",
      "92",
      "45",
      "47",
      "44",
      "46",
      "?!?"
     ],
     "answer": "46"
    },
    {
     "code": "a = [39, 58, 20, 55, 2]\nm = a[0]\nfor x in a:\n    if x > m:\n        m = x\nm",
     "prompt": "m neyi bulur? Değeri nedir?",
     "options": [
      "56",
      "58",
      "59",
      "116",
      "60",
      "57",
      "?!?"
     ],
     "answer": "58"
    },
    {
     "code": "a = [18, 34, 12, 57, 19]\nm = a[0]\nfor x in a:\n    if x > m:\n        m = x\nm",
     "prompt": "m neyi bulur? Değeri nedir?",
     "options": [
      "57",
      "56",
      "58",
      "114",
      "55",
      "59",
      "?!?"
     ],
     "answer": "57"
    }
   ]
  },
  {
   "id": "mid_23",
   "title": "Metin Metotları",
   "lesson": "count, replace, find, startswith gibi metotlar metni inceler.",
   "questions": [
    {
     "code": "'elma'.find('e')",
     "prompt": "find ilk bulunduğu indeksi verir. Sonuç nedir?",
     "options": [
      "0",
      "2",
      "10",
      "-1",
      "1",
      "-2",
      "?!?"
     ],
     "answer": "0"
    },
    {
     "code": "'elma'.find('m')",
     "prompt": "find ilk bulunduğu indeksi verir. Sonuç nedir?",
     "options": [
      "4",
      "1",
      "12",
      "0",
      "2",
      "3",
      "?!?"
     ],
     "answer": "2"
    },
    {
     "code": "'elma'.startswith('e')",
     "prompt": "startswith başlangıcı kontrol eder. Sonuç nedir?",
     "options": [
      "True",
      "0",
      "1",
      "None",
      "'True'",
      "False",
      "?!?"
     ],
     "answer": "True"
    }
   ]
  },
  {
   "id": "mid_24",
   "title": "split ve join",
   "lesson": "split metni listeye böler, join listeyi metne birleştirir.",
   "questions": [
    {
     "code": "len('python robot bilgi bahar'.split())",
     "prompt": "Kaç kelime var?",
     "options": [
      "3",
      "2",
      "6",
      "8",
      "5",
      "4",
      "?!?"
     ],
     "answer": "4"
    },
    {
     "code": "'-'.join(['dunya', 'python', 'bahar', 'bilgi'])",
     "prompt": "join listeyi tek metne birleştirir. Sonuç nedir?",
     "options": [
      "'DUNYA-PYTHON-BAHAR-BILGI'",
      "'dunya-python-bahar-bilgi'",
      "'dunya-python-bahar-bilg'",
      "'unya-python-bahar-bilgi'",
      "'iglib-rahab-nohtyp-aynud'",
      "'dunya-python-bahar-bilgid'",
      "?!?"
     ],
     "answer": "'dunya-python-bahar-bilgi'"
    },
    {
     "code": "'merhaba-robot-okul'.split('-')",
     "prompt": "split metni listeye böler. Sonuç nedir?",
     "options": [
      "['okul', 'robot', 'merhaba']",
      "[]",
      "['robot', 'okul']",
      "['merhaba', 'robot', 'okul', 'merhaba']",
      "['merhaba', 'robot', 'okul']",
      "['merhaba', 'robot']",
      "?!?"
     ],
     "answer": "['merhaba', 'robot', 'okul']"
    }
   ]
  },
  {
   "id": "mid_25",
   "title": "Sözlük Erişimi",
   "lesson": "Sözlük anahtar: değer çiftleri tutar; d[\"a\"] ile değere ulaşılır.",
   "questions": [
    {
     "code": "d = {'b': 2, 'c': 19, 'a': 27}\nd['b']",
     "prompt": "Sözlükte anahtar ile değere ulaşılır. Sonuç nedir?",
     "options": [
      "3",
      "0",
      "4",
      "1",
      "12",
      "2",
      "?!?"
     ],
     "answer": "2"
    },
    {
     "code": "d = {'a': 17, 'b': 21, 'c': 23}\nd['b']",
     "prompt": "Sözlükte anahtar ile değere ulaşılır. Sonuç nedir?",
     "options": [
      "20",
      "21",
      "19",
      "22",
      "42",
      "23",
      "?!?"
     ],
     "answer": "21"
    },
    {
     "code": "d = {'a': 21, 'c': 28, 'b': 12}\nd['b']",
     "prompt": "Sözlükte anahtar ile değere ulaşılır. Sonuç nedir?",
     "options": [
      "12",
      "11",
      "14",
      "13",
      "10",
      "24",
      "?!?"
     ],
     "answer": "12"
    }
   ]
  },
  {
   "id": "mid_26",
   "title": "Sözlüğü Güncelleme",
   "lesson": "d[anahtar] = değer ile ekleme/güncelleme yapılır; in anahtarı sorgular.",
   "questions": [
    {
     "code": "d = {'a': 2, 'b': 8}\n'b' in d",
     "prompt": "in sözlükte anahtara bakar. Sonuç nedir?",
     "options": [
      "True",
      "None",
      "1",
      "False",
      "0",
      "'True'",
      "?!?"
     ],
     "answer": "True"
    },
    {
     "code": "d = {'a': 8, 'b': 3}\nd['c'] = 3\nlen(d)",
     "prompt": "Yeni anahtar eklenince eleman sayısı artar. Sonuç nedir?",
     "options": [
      "5",
      "1",
      "4",
      "3",
      "2",
      "6",
      "?!?"
     ],
     "answer": "3"
    },
    {
     "code": "d = {'a': 2, 'b': 14}\n'b' in d",
     "prompt": "in sözlükte anahtara bakar. Sonuç nedir?",
     "options": [
      "0",
      "1",
      "False",
      "True",
      "'True'",
      "None",
      "?!?"
     ],
     "answer": "True"
    }
   ]
  },
  {
   "id": "mid_27",
   "title": "Sözlük Değerleri",
   "lesson": "values() değerleri, keys() anahtarları verir.",
   "questions": [
    {
     "code": "d = {'a': 7, 'b': 17, 'c': 4}\nsum(d.values())",
     "prompt": "values() değerleri verir. Sonuç nedir?",
     "options": [
      "56",
      "30",
      "28",
      "26",
      "27",
      "29",
      "?!?"
     ],
     "answer": "28"
    },
    {
     "code": "d = {'a': 7, 'b': 5, 'c': 16}\nlist(d.keys())",
     "prompt": "keys() anahtarları verir. Sonuç nedir?",
     "options": [
      "['c', 'b', 'a']",
      "['a', 'b']",
      "['a', 'b', 'c']",
      "['b', 'c']",
      "[]",
      "['a', 'b', 'c', 'a']",
      "?!?"
     ],
     "answer": "['a', 'b', 'c']"
    },
    {
     "code": "d = {'a': 8, 'b': 18, 'c': 9}\nlist(d.keys())",
     "prompt": "keys() anahtarları verir. Sonuç nedir?",
     "options": [
      "['c', 'b', 'a']",
      "[]",
      "['a', 'b', 'c', 'a']",
      "['a', 'b']",
      "['b', 'c']",
      "['a', 'b', 'c']",
      "?!?"
     ],
     "answer": "['a', 'b', 'c']"
    }
   ]
  },
  {
   "id": "mid_28",
   "title": "Karma Mantık İfadeleri",
   "lesson": "Parantezler ve not/and/or birlikte kullanılabilir.",
   "questions": [
    {
     "code": "not (1 > 4) and (1 == 1 or 5 > 5)",
     "prompt": "Önce parantezler, sonra not, and, or. Sonuç nedir?",
     "options": [
      "None",
      "'True'",
      "0",
      "1",
      "True",
      "False",
      "?!?"
     ],
     "answer": "True"
    },
    {
     "code": "(5 > 1 or 7 < 6) and not (6 == 5)",
     "prompt": "Önce parantezler, sonra not, and, or. Sonuç nedir?",
     "options": [
      "False",
      "True",
      "'True'",
      "None",
      "0",
      "1",
      "?!?"
     ],
     "answer": "True"
    },
    {
     "code": "not (1 > 5) and (7 == 4 or 5 > 4)",
     "prompt": "Önce parantezler, sonra not, and, or. Sonuç nedir?",
     "options": [
      "'True'",
      "None",
      "True",
      "1",
      "False",
      "0",
      "?!?"
     ],
     "answer": "True"
    }
   ]
  },
  {
   "id": "mid_29",
   "title": "Değişken Kapsamı",
   "lesson": "Fonksiyonun içinde tanımlanan değişken yereldir, dışarıyı etkilemez.",
   "questions": [
    {
     "code": "x = 2\ndef f():\n    x = 13\n    return x\n\nf() + x",
     "prompt": "Fonksiyon içindeki x yereldir. Sonuç nedir?",
     "options": [
      "4",
      "26",
      "14",
      "17",
      "16",
      "15",
      "?!?"
     ],
     "answer": "15"
    },
    {
     "code": "x = 3\ndef f():\n    x = 12\n    return x\n\nf() + x",
     "prompt": "Fonksiyon içindeki x yereldir. Sonuç nedir?",
     "options": [
      "6",
      "24",
      "15",
      "16",
      "17",
      "14",
      "?!?"
     ],
     "answer": "15"
    },
    {
     "code": "x = 1\ndef f():\n    x = 19\n    return x\n\nf() + x",
     "prompt": "Fonksiyon içindeki x yereldir. Sonuç nedir?",
     "options": [
      "2",
      "19",
      "22",
      "21",
      "20",
      "38",
      "?!?"
     ],
     "answer": "20"
    }
   ]
  },
  {
   "id": "mid_30",
   "title": "Liste Üreteci",
   "lesson": "[ifade for x in ...] tek satırda liste üretir.",
   "questions": [
    {
     "code": "[x * 2 for x in range(3)]",
     "prompt": "Liste üreteci her x için ifadeyi hesaplar. Sonuç nedir?",
     "options": [
      "[0, 2, 4, 0]",
      "[0, 2, 4]",
      "[4, 2, 0]",
      "[0, 2]",
      "[]",
      "[2, 4]",
      "?!?"
     ],
     "answer": "[0, 2, 4]"
    },
    {
     "code": "[x * 2 for x in range(5)]",
     "prompt": "Liste üreteci her x için ifadeyi hesaplar. Sonuç nedir?",
     "options": [
      "[0, 2, 4, 6]",
      "[0, 2, 4, 6, 8, 0]",
      "[0, 2, 4, 6, 8]",
      "[2, 4, 6, 8]",
      "[8, 6, 4, 2, 0]",
      "[]",
      "?!?"
     ],
     "answer": "[0, 2, 4, 6, 8]"
    },
    {
     "code": "[x for x in [8, 17, 20, 1, 9, 19] if x > 7]",
     "prompt": "Koşulu sağlayanlar listeye girer. Sonuç nedir?",
     "options": [
      "[8, 17, 20, 9, 19]",
      "[17, 20, 9, 19]",
      "[8, 17, 20, 9, 19, 8]",
      "[19, 9, 20, 17, 8]",
      "[8, 17, 20, 9]",
      "[]",
      "?!?"
     ],
     "answer": "[8, 17, 20, 9, 19]"
    }
   ]
  },
  {
   "id": "mid_31",
   "title": "zip ile Eşleştirme",
   "lesson": "zip iki listeyi eleman eleman eşleştirir.",
   "questions": [
    {
     "code": "[x + y for x, y in zip([1, 6, 2], [9, 4, 3])]",
     "prompt": "zip iki listeyi eşleştirir. Sonuç nedir?",
     "options": [
      "[10, 5]",
      "[5, 10, 10]",
      "[10, 10, 5]",
      "[10, 10, 5, 10]",
      "[10, 10]",
      "[]",
      "?!?"
     ],
     "answer": "[10, 10, 5]"
    },
    {
     "code": "[x + y for x, y in zip([5, 3, 7], [5, 3, 1])]",
     "prompt": "zip iki listeyi eşleştirir. Sonuç nedir?",
     "options": [
      "[10, 6, 8]",
      "[8, 6, 10]",
      "[6, 8]",
      "[10, 6]",
      "[10, 6, 8, 10]",
      "[]",
      "?!?"
     ],
     "answer": "[10, 6, 8]"
    },
    {
     "code": "[x + y for x, y in zip([2, 1, 3], [4, 1, 7])]",
     "prompt": "zip iki listeyi eşleştirir. Sonuç nedir?",
     "options": [
      "[6, 2]",
      "[6, 2, 10]",
      "[10, 2, 6]",
      "[6, 2, 10, 6]",
      "[2, 10]",
      "[]",
      "?!?"
     ],
     "answer": "[6, 2, 10]"
    }
   ]
  },
  {
   "id": "mid_32",
   "title": "f-string",
   "lesson": "f\"...\" içinde {ifade} yazarak değerleri metne yerleştirirsin.",
   "questions": [
    {
     "code": "a = 3\nb = 8\nf'{a} + {b} = {a + b}'",
     "prompt": "f-string içindeki ifadeler hesaplanır. Sonuç nedir?",
     "options": [
      "'3 + 8 = 113'",
      "'3 + 8 = 1'",
      "''",
      "'11 = 8 + 3'",
      "' + 8 = 11'",
      "'3 + 8 = 11'",
      "?!?"
     ],
     "answer": "'3 + 8 = 11'"
    },
    {
     "code": "a = 6\nb = 3\nf'{a} + {b} = {a + b}'",
     "prompt": "f-string içindeki ifadeler hesaplanır. Sonuç nedir?",
     "options": [
      "'6 + 3 = 9'",
      "'6 + 3 = '",
      "'9 = 3 + 6'",
      "''",
      "'6 + 3 = 96'",
      "' + 3 = 9'",
      "?!?"
     ],
     "answer": "'6 + 3 = 9'"
    },
    {
     "code": "a = 5\nb = 7\nf'{a} + {b} = {a + b}'",
     "prompt": "f-string içindeki ifadeler hesaplanır. Sonuç nedir?",
     "options": [
      "''",
      "' + 7 = 12'",
      "'5 + 7 = 12'",
      "'21 = 7 + 5'",
      "'5 + 7 = 125'",
      "'5 + 7 = 1'",
      "?!?"
     ],
     "answer": "'5 + 7 = 12'"
    }
   ]
  },
  {
   "id": "mid_33",
   "title": "Çarpım Biriktirme",
   "lesson": "Döngüde değişkeni çarparak faktöriyel gibi sonuçlar hesaplanır.",
   "questions": [
    {
     "code": "t = 1\nfor i in range(1, 6):\n    t *= i\nt",
     "prompt": "t nedir?",
     "options": [
      "121",
      "240",
      "120",
      "119",
      "118",
      "122",
      "?!?"
     ],
     "answer": "120"
    },
    {
     "code": "t = 1\nfor i in range(1, 5):\n    t *= i\nt",
     "prompt": "t nedir?",
     "options": [
      "23",
      "24",
      "48",
      "22",
      "26",
      "25",
      "?!?"
     ],
     "answer": "24"
    },
    {
     "code": "t = 1\nfor i in range(1, 7):\n    t *= i\nt",
     "prompt": "t nedir?",
     "options": [
      "1440",
      "720",
      "719",
      "721",
      "718",
      "722",
      "?!?"
     ],
     "answer": "720"
    }
   ]
  }
 ],
 "hard": [
  {
   "id": "hard_1",
   "title": "Koşul Akışını İzleme",
   "lesson": "Kodu satır satır izle: iç koşul yalnızca dış koşul doğruysa denenir.",
   "questions": [
    {
     "code": "x = 6\ny = 4\nif x > 1:\n    if y < 3:\n        x = x + y\nx",
     "prompt": "x'in son değeri nedir?",
     "options": [
      "6",
      "4",
      "12",
      "7",
      "8",
      "5",
      "?!?"
     ],
     "answer": "6"
    },
    {
     "code": "x = 3\ny = 2\nif x > 1:\n    if y < 3:\n        x = x + y\nx",
     "prompt": "x'in son değeri nedir?",
     "options": [
      "5",
      "6",
      "7",
      "10",
      "3",
      "4",
      "?!?"
     ],
     "answer": "5"
    },
    {
     "code": "x = 4\ny = 2\nif x > 1:\n    if y < 3:\n        x = x + y\nx",
     "prompt": "x'in son değeri nedir?",
     "options": [
      "12",
      "5",
      "7",
      "8",
      "6",
      "4",
      "?!?"
     ],
     "answer": "6"
    }
   ]
  },
  {
   "id": "hard_2",
   "title": "İç İçe Döngü Sayısı",
   "lesson": "İç döngü her dış turda baştan çalışır: toplam tur = dış × iç.",
   "questions": [
    {
     "code": "c = 0\nfor i in range(4):\n    for j in range(4):\n        c += 1\nc",
     "prompt": "İç döngü her dış turda tamamen çalışır. c nedir?",
     "options": [
      "16",
      "15",
      "18",
      "17",
      "14",
      "32",
      "?!?"
     ],
     "answer": "16"
    },
    {
     "code": "c = 0\nfor i in range(2):\n    for j in range(3):\n        c += 1\nc",
     "prompt": "İç döngü her dış turda tamamen çalışır. c nedir?",
     "options": [
      "8",
      "12",
      "4",
      "6",
      "7",
      "5",
      "?!?"
     ],
     "answer": "6"
    },
    {
     "code": "c = 0\nfor i in range(3):\n    for j in range(3):\n        c += 1\nc",
     "prompt": "İç döngü her dış turda tamamen çalışır. c nedir?",
     "options": [
      "11",
      "8",
      "9",
      "7",
      "18",
      "10",
      "?!?"
     ],
     "answer": "9"
    }
   ]
  },
  {
   "id": "hard_3",
   "title": "İç İçe Döngü Toplamı",
   "lesson": "İç içe döngülerde i ve j birlikte değişir; her çiftte işlem yapılır.",
   "questions": [
    {
     "code": "t = 0\nfor i in range(4):\n    for j in range(2):\n        t += i * j\nt",
     "prompt": "t nedir?",
     "options": [
      "7",
      "5",
      "4",
      "8",
      "6",
      "12",
      "?!?"
     ],
     "answer": "6"
    },
    {
     "code": "t = 0\nfor i in range(2):\n    for j in range(3):\n        t += i * j\nt",
     "prompt": "t nedir?",
     "options": [
      "4",
      "5",
      "1",
      "6",
      "2",
      "3",
      "?!?"
     ],
     "answer": "3"
    },
    {
     "code": "t = 0\nfor i in range(3):\n    for j in range(3):\n        t += i * j\nt",
     "prompt": "t nedir?",
     "options": [
      "18",
      "9",
      "7",
      "11",
      "10",
      "8",
      "?!?"
     ],
     "answer": "9"
    }
   ]
  },
  {
   "id": "hard_4",
   "title": "Döngü, Koşul ve break",
   "lesson": "break döngüyü durdurduğunda değişkenin son değeri kalır.",
   "questions": [
    {
     "code": "for i in range(2, 20):\n    if i * i > 50:\n        break\ni - 1",
     "prompt": "Döngü ilk uygun i’de durur. Sonuç nedir?",
     "options": [
      "8",
      "9",
      "5",
      "7",
      "14",
      "6",
      "?!?"
     ],
     "answer": "7"
    },
    {
     "code": "for i in range(2, 20):\n    if i * i > 15:\n        break\ni - 1",
     "prompt": "Döngü ilk uygun i’de durur. Sonuç nedir?",
     "options": [
      "3",
      "6",
      "5",
      "4",
      "2",
      "1",
      "?!?"
     ],
     "answer": "3"
    },
    {
     "code": "for i in range(2, 20):\n    if i * i > 23:\n        break\ni - 1",
     "prompt": "Döngü ilk uygun i’de durur. Sonuç nedir?",
     "options": [
      "6",
      "5",
      "2",
      "8",
      "4",
      "3",
      "?!?"
     ],
     "answer": "4"
    }
   ]
  },
  {
   "id": "hard_5",
   "title": "Basamak Toplamı",
   "lesson": "% 10 son basamağı, // 10 son basamağı atar.",
   "questions": [
    {
     "code": "n = 604\ns = 0\nwhile n > 0:\n    s += n % 10\n    n //= 10\ns",
     "prompt": "Basamakları toplar. s nedir?",
     "options": [
      "8",
      "11",
      "9",
      "20",
      "12",
      "10",
      "?!?"
     ],
     "answer": "10"
    },
    {
     "code": "n = 146\ns = 0\nwhile n > 0:\n    s += n % 10\n    n //= 10\ns",
     "prompt": "Basamakları toplar. s nedir?",
     "options": [
      "9",
      "22",
      "11",
      "12",
      "13",
      "10",
      "?!?"
     ],
     "answer": "11"
    },
    {
     "code": "n = 162\ns = 0\nwhile n > 0:\n    s += n % 10\n    n //= 10\ns",
     "prompt": "Basamakları toplar. s nedir?",
     "options": [
      "11",
      "7",
      "18",
      "10",
      "8",
      "9",
      "?!?"
     ],
     "answer": "9"
    }
   ]
  },
  {
   "id": "hard_6",
   "title": "Fonksiyon + Döngü",
   "lesson": "Döngüyü fonksiyonun içine koyarak yeniden kullanılabilir hesaplar yaparsın.",
   "questions": [
    {
     "code": "def topla(n):\n    t = 0\n    for i in range(1, n + 1):\n        t += i\n    return t\n\ntopla(3)",
     "prompt": "Fonksiyon 1’den n’ye kadar toplar. Sonuç nedir?",
     "options": [
      "4",
      "7",
      "8",
      "5",
      "6",
      "12",
      "?!?"
     ],
     "answer": "6"
    },
    {
     "code": "def topla(n):\n    t = 0\n    for i in range(1, n + 1):\n        t += i\n    return t\n\ntopla(8)",
     "prompt": "Fonksiyon 1’den n’ye kadar toplar. Sonuç nedir?",
     "options": [
      "37",
      "35",
      "38",
      "72",
      "34",
      "36",
      "?!?"
     ],
     "answer": "36"
    },
    {
     "code": "def topla(n):\n    t = 0\n    for i in range(1, n + 1):\n        t += i\n    return t\n\ntopla(4)",
     "prompt": "Fonksiyon 1’den n’ye kadar toplar. Sonuç nedir?",
     "options": [
      "9",
      "8",
      "20",
      "11",
      "10",
      "12",
      "?!?"
     ],
     "answer": "10"
    }
   ]
  },
  {
   "id": "hard_7",
   "title": "Fonksiyon + Liste",
   "lesson": "Fonksiyona liste verip içinde dolaşabilirsin.",
   "questions": [
    {
     "code": "def enb(l):\n    m = l[0]\n    for x in l:\n        if x > m:\n            m = x\n    return m\n\nenb([39, 46, 18, 21, 29])",
     "prompt": "Fonksiyon en büyüğü bulur. Sonuç nedir?",
     "options": [
      "45",
      "47",
      "44",
      "46",
      "92",
      "48",
      "?!?"
     ],
     "answer": "46"
    },
    {
     "code": "def enb(l):\n    m = l[0]\n    for x in l:\n        if x > m:\n            m = x\n    return m\n\nenb([51, 56, 29, 7, 19])",
     "prompt": "Fonksiyon en büyüğü bulur. Sonuç nedir?",
     "options": [
      "55",
      "57",
      "112",
      "58",
      "56",
      "54",
      "?!?"
     ],
     "answer": "56"
    },
    {
     "code": "def enb(l):\n    m = l[0]\n    for x in l:\n        if x > m:\n            m = x\n    return m\n\nenb([21, 33, 38, 17, 30])",
     "prompt": "Fonksiyon en büyüğü bulur. Sonuç nedir?",
     "options": [
      "39",
      "76",
      "38",
      "40",
      "36",
      "37",
      "?!?"
     ],
     "answer": "38"
    }
   ]
  },
  {
   "id": "hard_8",
   "title": "Özyineleme: Faktöriyel",
   "lesson": "Fonksiyon kendini küçük bir değerle çağırır; durma koşulu şarttır.",
   "questions": [
    {
     "code": "def f(n):\n    if n == 0:\n        return 1\n    return n * f(n - 1)\n\nf(6)",
     "prompt": "Özyineleme: fonksiyon kendini çağırır. Sonuç nedir?",
     "options": [
      "1440",
      "719",
      "720",
      "721",
      "722",
      "718",
      "?!?"
     ],
     "answer": "720"
    },
    {
     "code": "def f(n):\n    if n == 0:\n        return 1\n    return n * f(n - 1)\n\nf(4)",
     "prompt": "Özyineleme: fonksiyon kendini çağırır. Sonuç nedir?",
     "options": [
      "22",
      "26",
      "48",
      "25",
      "23",
      "24",
      "?!?"
     ],
     "answer": "24"
    },
    {
     "code": "def f(n):\n    if n == 0:\n        return 1\n    return n * f(n - 1)\n\nf(5)",
     "prompt": "Özyineleme: fonksiyon kendini çağırır. Sonuç nedir?",
     "options": [
      "120",
      "118",
      "122",
      "121",
      "119",
      "240",
      "?!?"
     ],
     "answer": "120"
    }
   ]
  },
  {
   "id": "hard_9",
   "title": "Özyineleme: Toplam",
   "lesson": "n + toplam(n-1) mantığıyla özyinelemeli toplama.",
   "questions": [
    {
     "code": "def t(n):\n    if n == 0:\n        return 0\n    return n + t(n - 1)\n\nt(3)",
     "prompt": "Özyinelemeli toplam. Sonuç nedir?",
     "options": [
      "12",
      "4",
      "6",
      "8",
      "5",
      "7",
      "?!?"
     ],
     "answer": "6"
    },
    {
     "code": "def t(n):\n    if n == 0:\n        return 0\n    return n + t(n - 1)\n\nt(6)",
     "prompt": "Özyinelemeli toplam. Sonuç nedir?",
     "options": [
      "21",
      "22",
      "42",
      "20",
      "19",
      "23",
      "?!?"
     ],
     "answer": "21"
    },
    {
     "code": "def t(n):\n    if n == 0:\n        return 0\n    return n + t(n - 1)\n\nt(5)",
     "prompt": "Özyinelemeli toplam. Sonuç nedir?",
     "options": [
      "16",
      "14",
      "30",
      "15",
      "17",
      "13",
      "?!?"
     ],
     "answer": "15"
    }
   ]
  },
  {
   "id": "hard_10",
   "title": "Özyineleme: Fibonacci",
   "lesson": "fib(n) = fib(n-1) + fib(n-2); ilk iki terim 0 ve 1’dir.",
   "questions": [
    {
     "code": "def fib(n):\n    if n < 2:\n        return n\n    return fib(n - 1) + fib(n - 2)\n\nfib(5)",
     "prompt": "Fibonacci: 0, 1, 1, 2, 3, 5, ... Sonuç nedir?",
     "options": [
      "10",
      "6",
      "4",
      "5",
      "7",
      "3",
      "?!?"
     ],
     "answer": "5"
    },
    {
     "code": "def fib(n):\n    if n < 2:\n        return n\n    return fib(n - 1) + fib(n - 2)\n\nfib(6)",
     "prompt": "Fibonacci: 0, 1, 1, 2, 3, 5, ... Sonuç nedir?",
     "options": [
      "7",
      "6",
      "10",
      "8",
      "9",
      "16",
      "?!?"
     ],
     "answer": "8"
    },
    {
     "code": "def fib(n):\n    if n < 2:\n        return n\n    return fib(n - 1) + fib(n - 2)\n\nfib(9)",
     "prompt": "Fibonacci: 0, 1, 1, 2, 3, 5, ... Sonuç nedir?",
     "options": [
      "68",
      "36",
      "35",
      "34",
      "33",
      "32",
      "?!?"
     ],
     "answer": "34"
    }
   ]
  },
  {
   "id": "hard_11",
   "title": "Koşullu Liste Üreteci",
   "lesson": "Liste üretecine if ekleyerek sadece uygun elemanları alabilirsin.",
   "questions": [
    {
     "code": "[x * x for x in range(8) if x % 2 == 1]",
     "prompt": "Koşullu liste üreteci. Sonuç nedir?",
     "options": [
      "[1, 9, 25, 49]",
      "[1, 9, 25]",
      "[]",
      "[1, 9, 25, 49, 1]",
      "[9, 25, 49]",
      "[49, 25, 9, 1]",
      "?!?"
     ],
     "answer": "[1, 9, 25, 49]"
    },
    {
     "code": "[x * x for x in range(9) if x % 2 == 1]",
     "prompt": "Koşullu liste üreteci. Sonuç nedir?",
     "options": [
      "[1, 9, 25]",
      "[1, 9, 25, 49, 1]",
      "[]",
      "[9, 25, 49]",
      "[49, 25, 9, 1]",
      "[1, 9, 25, 49]",
      "?!?"
     ],
     "answer": "[1, 9, 25, 49]"
    },
    {
     "code": "[x * x for x in range(6) if x % 2 == 1]",
     "prompt": "Koşullu liste üreteci. Sonuç nedir?",
     "options": [
      "[25, 9, 1]",
      "[1, 9, 25, 1]",
      "[1, 9, 25]",
      "[]",
      "[9, 25]",
      "[1, 9]",
      "?!?"
     ],
     "answer": "[1, 9, 25]"
    }
   ]
  },
  {
   "id": "hard_12",
   "title": "Matris (Liste İçinde Liste)",
   "lesson": "m[satır][sütun] ile iç içe listenin elemanına ulaşılır.",
   "questions": [
    {
     "code": "m = [[3, 9, 1], [9, 1, 7], [3, 4, 5]]\nm[0][0] + m[1][1] + m[2][2]",
     "prompt": "Köşegen toplamı nedir?",
     "options": [
      "9",
      "7",
      "8",
      "10",
      "11",
      "18",
      "?!?"
     ],
     "answer": "9"
    },
    {
     "code": "m = [[8, 6, 9], [1, 3, 2], [2, 1, 6]]\nm[0][0] + m[1][1] + m[2][2]",
     "prompt": "Köşegen toplamı nedir?",
     "options": [
      "15",
      "19",
      "34",
      "18",
      "17",
      "16",
      "?!?"
     ],
     "answer": "17"
    },
    {
     "code": "m = [[2, 6, 8], [5, 6, 7], [9, 8, 4]]\nm[2][1]",
     "prompt": "İlk indeks satır, ikinci sütundur. Sonuç nedir?",
     "options": [
      "16",
      "6",
      "8",
      "9",
      "10",
      "7",
      "?!?"
     ],
     "answer": "8"
    }
   ]
  },
  {
   "id": "hard_13",
   "title": "Adımlı Dilimleme",
   "lesson": "[::2] bir atlar, [::-1] ters çevirir.",
   "questions": [
    {
     "code": "a = [15, 31, 34, 22, 14, 18]\na[::2]",
     "prompt": "[::2] bir atlayarak alır. Sonuç nedir?",
     "options": [
      "[15, 34, 14, 15]",
      "[14, 34, 15]",
      "[15, 34, 14]",
      "[]",
      "[34, 14]",
      "[15, 34]",
      "?!?"
     ],
     "answer": "[15, 34, 14]"
    },
    {
     "code": "a = [6, 14, 44, 26, 41, 42]\na[1::2]",
     "prompt": "[1::2] 1’den başlayıp ikişer atlar. Sonuç nedir?",
     "options": [
      "[]",
      "[14, 26, 42]",
      "[14, 26, 42, 14]",
      "[14, 26]",
      "[26, 42]",
      "[42, 26, 14]",
      "?!?"
     ],
     "answer": "[14, 26, 42]"
    },
    {
     "code": "'bahar'[::-1]",
     "prompt": "[::-1] ters çevirir. Sonuç nedir?",
     "options": [
      "'rahabr'",
      "'RAHAB'",
      "'ahab'",
      "'bahar'",
      "'raha'",
      "'rahab'",
      "?!?"
     ],
     "answer": "'rahab'"
    }
   ]
  },
  {
   "id": "hard_14",
   "title": "Sözlükle Sayma",
   "lesson": "d.get(k, 0) + 1 deseni, her öğenin kaç kez geçtiğini sayar.",
   "questions": [
    {
     "code": "d = {}\nfor c in 'aabbb':\n    d[c] = d.get(c, 0) + 1\nd['a']",
     "prompt": "Sözlük harfleri sayar. Sonuç nedir?",
     "options": [
      "3",
      "0",
      "4",
      "12",
      "2",
      "1",
      "?!?"
     ],
     "answer": "2"
    },
    {
     "code": "d = {}\nfor c in 'kokok':\n    d[c] = d.get(c, 0) + 1\nd['k']",
     "prompt": "Sözlük harfleri sayar. Sonuç nedir?",
     "options": [
      "5",
      "1",
      "2",
      "3",
      "6",
      "4",
      "?!?"
     ],
     "answer": "3"
    },
    {
     "code": "d = {}\nfor c in 'ababa':\n    d[c] = d.get(c, 0) + 1\nd['a']",
     "prompt": "Sözlük harfleri sayar. Sonuç nedir?",
     "options": [
      "2",
      "3",
      "1",
      "4",
      "5",
      "6",
      "?!?"
     ],
     "answer": "3"
    }
   ]
  },
  {
   "id": "hard_15",
   "title": "get ile Varsayılan Değer",
   "lesson": "d.get(anahtar, varsayılan) anahtar yoksa hata vermez.",
   "questions": [
    {
     "code": "d = {'a': 6, 'b': 13}\nd.get('b', 0) + d.get('x', 5)",
     "prompt": "Sonuç nedir?",
     "options": [
      "16",
      "19",
      "17",
      "18",
      "20",
      "36",
      "?!?"
     ],
     "answer": "18"
    },
    {
     "code": "d = {'a': 3}\nd.get('z', 0)",
     "prompt": "get yoksa varsayılanı verir. Sonuç nedir?",
     "options": [
      "1",
      "10",
      "-1",
      "-2",
      "0",
      "2",
      "?!?"
     ],
     "answer": "0"
    },
    {
     "code": "d = {'a': 8}\nd.get('z', 0)",
     "prompt": "get yoksa varsayılanı verir. Sonuç nedir?",
     "options": [
      "2",
      "-2",
      "1",
      "10",
      "-1",
      "0",
      "?!?"
     ],
     "answer": "0"
    }
   ]
  },
  {
   "id": "hard_16",
   "title": "Palindrom Kontrolü",
   "lesson": "Bir metin tersine eşitse palindromdur.",
   "questions": [
    {
     "code": "s = 'masa'\ns == s[::-1]",
     "prompt": "Palindrom, tersi kendisine eşit olandır. Sonuç nedir?",
     "options": [
      "None",
      "True",
      "'True'",
      "1",
      "0",
      "False",
      "?!?"
     ],
     "answer": "False"
    },
    {
     "code": "s = 'kayak'\ns == s[::-1]",
     "prompt": "Palindrom, tersi kendisine eşit olandır. Sonuç nedir?",
     "options": [
      "0",
      "'True'",
      "None",
      "False",
      "1",
      "True",
      "?!?"
     ],
     "answer": "True"
    },
    {
     "code": "s = 'level'\ns == s[::-1]",
     "prompt": "Palindrom, tersi kendisine eşit olandır. Sonuç nedir?",
     "options": [
      "0",
      "None",
      "1",
      "False",
      "True",
      "'True'",
      "?!?"
     ],
     "answer": "True"
    }
   ]
  },
  {
   "id": "hard_17",
   "title": "Fonksiyon Zinciri",
   "lesson": "f(g(x)) içte g, sonra f çalışır; sıra sonucu değiştirir.",
   "questions": [
    {
     "code": "def f(x):\n    return x + 1\ndef g(x):\n    return x * 2\n\ng(f(9))",
     "prompt": "İçteki fonksiyon önce çalışır. Sonuç nedir?",
     "options": [
      "19",
      "22",
      "21",
      "40",
      "18",
      "20",
      "?!?"
     ],
     "answer": "20"
    },
    {
     "code": "def f(x):\n    return x + 1\ndef g(x):\n    return x * 2\n\ng(f(7))",
     "prompt": "İçteki fonksiyon önce çalışır. Sonuç nedir?",
     "options": [
      "18",
      "32",
      "14",
      "16",
      "15",
      "17",
      "?!?"
     ],
     "answer": "16"
    },
    {
     "code": "def f(x):\n    return x + 1\ndef g(x):\n    return x * 2\n\nf(g(9))",
     "prompt": "İçteki fonksiyon önce çalışır. Sonuç nedir?",
     "options": [
      "19",
      "21",
      "17",
      "20",
      "18",
      "38",
      "?!?"
     ],
     "answer": "19"
    }
   ]
  },
  {
   "id": "hard_18",
   "title": "global Değişken",
   "lesson": "global bildirimi fonksiyonun dış değişkeni değiştirmesini sağlar.",
   "questions": [
    {
     "code": "n = 0\ndef art():\n    global n\n    n += 4\n\nart()\nart()\nn",
     "prompt": "global ile dış değişken değişir. n nedir?",
     "options": [
      "7",
      "8",
      "10",
      "6",
      "16",
      "9",
      "?!?"
     ],
     "answer": "8"
    },
    {
     "code": "n = 0\ndef art():\n    global n\n    n += 5\n\nart()\nart()\nn",
     "prompt": "global ile dış değişken değişir. n nedir?",
     "options": [
      "12",
      "8",
      "10",
      "9",
      "11",
      "20",
      "?!?"
     ],
     "answer": "10"
    },
    {
     "code": "n = 0\ndef art():\n    global n\n    n += 3\n\nart()\nart()\nn",
     "prompt": "global ile dış değişken değişir. n nedir?",
     "options": [
      "5",
      "12",
      "8",
      "4",
      "6",
      "7",
      "?!?"
     ],
     "answer": "6"
    }
   ]
  },
  {
   "id": "hard_19",
   "title": "Değer Takası",
   "lesson": "İki değişkenin yerini geçici değişkenle ya da a, b = b, a ile değiştirirsin.",
   "questions": [
    {
     "code": "a = 8\nb = 17\na, b = b, a\na - b",
     "prompt": "Yer değiştirmeden sonra a - b nedir?",
     "options": [
      "10",
      "9",
      "8",
      "18",
      "7",
      "11",
      "?!?"
     ],
     "answer": "9"
    },
    {
     "code": "a = 8\nb = 19\na, b = b, a\na - b",
     "prompt": "Yer değiştirmeden sonra a - b nedir?",
     "options": [
      "10",
      "12",
      "22",
      "13",
      "9",
      "11",
      "?!?"
     ],
     "answer": "11"
    },
    {
     "code": "a = 7\nb = 19\nt = a\na = b\nb = t\na - b",
     "prompt": "Yer değiştirmeden sonra a - b nedir?",
     "options": [
      "24",
      "11",
      "10",
      "14",
      "12",
      "13",
      "?!?"
     ],
     "answer": "12"
    }
   ]
  },
  {
   "id": "hard_20",
   "title": "Sıralama ve İndeks",
   "lesson": "sorted ve index birlikte kullanılabilir.",
   "questions": [
    {
     "code": "sorted([37, 2, 57, 11, 46])[1]",
     "prompt": "Sıralanmış listenin 1. indeksi nedir?",
     "options": [
      "12",
      "10",
      "13",
      "22",
      "11",
      "9",
      "?!?"
     ],
     "answer": "11"
    },
    {
     "code": "sorted([13, 12, 39, 10, 57], reverse=True)[0]",
     "prompt": "reverse=True büyükten küçüğe sıralar. Sonuç nedir?",
     "options": [
      "55",
      "114",
      "57",
      "56",
      "59",
      "58",
      "?!?"
     ],
     "answer": "57"
    },
    {
     "code": "sorted([3, 55, 41, 58, 36], reverse=True)[0]",
     "prompt": "reverse=True büyükten küçüğe sıralar. Sonuç nedir?",
     "options": [
      "58",
      "60",
      "59",
      "116",
      "56",
      "57",
      "?!?"
     ],
     "answer": "58"
    }
   ]
  },
  {
   "id": "hard_21",
   "title": "Küme (set)",
   "lesson": "set tekrarlı elemanları tek tek tutar.",
   "questions": [
    {
     "code": "sorted(set([6, 5, 4, 4, 6]))",
     "prompt": "set tekrarsızdır, sorted sıralar. Sonuç nedir?",
     "options": [
      "[5, 6]",
      "[4, 5]",
      "[4, 5, 6, 4]",
      "[]",
      "[6, 5, 4]",
      "[4, 5, 6]",
      "?!?"
     ],
     "answer": "[4, 5, 6]"
    },
    {
     "code": "sorted(set([4, 4, 2, 2, 5]))",
     "prompt": "set tekrarsızdır, sorted sıralar. Sonuç nedir?",
     "options": [
      "[2, 4]",
      "[]",
      "[2, 4, 5]",
      "[5, 4, 2]",
      "[2, 4, 5, 2]",
      "[4, 5]",
      "?!?"
     ],
     "answer": "[2, 4, 5]"
    },
    {
     "code": "len(set([5, 7, 6, 5, 6]))",
     "prompt": "set tekrarları atar. Sonuç nedir?",
     "options": [
      "2",
      "1",
      "6",
      "5",
      "4",
      "3",
      "?!?"
     ],
     "answer": "3"
    }
   ]
  },
  {
   "id": "hard_22",
   "title": "Tuple",
   "lesson": "Tuple değişmez bir listedir; x, y, z = t ile açılır.",
   "questions": [
    {
     "code": "t = (16, 6, 14)\nx, y, z = t\ny + z",
     "prompt": "Tuple açılımı sırayla atar. Sonuç nedir?",
     "options": [
      "21",
      "18",
      "20",
      "40",
      "19",
      "22",
      "?!?"
     ],
     "answer": "20"
    },
    {
     "code": "t = (17, 1, 2)\nx, y, z = t\ny + z",
     "prompt": "Tuple açılımı sırayla atar. Sonuç nedir?",
     "options": [
      "5",
      "1",
      "2",
      "3",
      "4",
      "6",
      "?!?"
     ],
     "answer": "3"
    },
    {
     "code": "t = (5, 2, 8)\nx, y, z = t\ny + z",
     "prompt": "Tuple açılımı sırayla atar. Sonuç nedir?",
     "options": [
      "20",
      "11",
      "9",
      "10",
      "8",
      "12",
      "?!?"
     ],
     "answer": "10"
    }
   ]
  },
  {
   "id": "hard_23",
   "title": "lambda ve map",
   "lesson": "lambda kısa fonksiyon yazar; map her elemana uygular.",
   "questions": [
    {
     "code": "(lambda a, b: a + b)(7, 6)",
     "prompt": "lambda küçük bir fonksiyondur. Sonuç nedir?",
     "options": [
      "13",
      "15",
      "11",
      "14",
      "26",
      "12",
      "?!?"
     ],
     "answer": "13"
    },
    {
     "code": "list(map(lambda x: x * 2, [1, 4, 7]))",
     "prompt": "map her elemana fonksiyonu uygular. Sonuç nedir?",
     "options": [
      "[2, 8, 14]",
      "[]",
      "[8, 14]",
      "[2, 8, 14, 2]",
      "[14, 8, 2]",
      "[2, 8]",
      "?!?"
     ],
     "answer": "[2, 8, 14]"
    },
    {
     "code": "(lambda a, b: a + b)(4, 4)",
     "prompt": "lambda küçük bir fonksiyondur. Sonuç nedir?",
     "options": [
      "9",
      "16",
      "6",
      "10",
      "7",
      "8",
      "?!?"
     ],
     "answer": "8"
    }
   ]
  },
  {
   "id": "hard_24",
   "title": "Üreteç ile Toplam",
   "lesson": "sum(x for ... if ...) koşullu toplamı tek satırda hesaplar.",
   "questions": [
    {
     "code": "sum(x for x in range(12) if x % 3 == 0)",
     "prompt": "3’e bölünenleri toplar. Sonuç nedir?",
     "options": [
      "19",
      "16",
      "36",
      "18",
      "20",
      "17",
      "?!?"
     ],
     "answer": "18"
    },
    {
     "code": "sum(x for x in range(20) if x % 3 == 0)",
     "prompt": "3’e bölünenleri toplar. Sonuç nedir?",
     "options": [
      "64",
      "62",
      "126",
      "63",
      "61",
      "65",
      "?!?"
     ],
     "answer": "63"
    },
    {
     "code": "sum(x for x in range(19) if x % 3 == 0)",
     "prompt": "3’e bölünenleri toplar. Sonuç nedir?",
     "options": [
      "61",
      "62",
      "63",
      "65",
      "126",
      "64",
      "?!?"
     ],
     "answer": "63"
    }
   ]
  },
  {
   "id": "hard_25",
   "title": "try / except",
   "lesson": "Hata oluşabilecek kod try içine yazılır; hata olursa except çalışır.",
   "questions": [
    {
     "code": "try:\n    n = int('abc')\nexcept ValueError:\n    n = -1\nn",
     "prompt": "Hata olursa except bloğu çalışır. n nedir?",
     "options": [
      "-1",
      "-2",
      "1",
      "-3",
      "9",
      "0",
      "?!?"
     ],
     "answer": "-1"
    },
    {
     "code": "try:\n    n = int('x9')\nexcept ValueError:\n    n = -1\nn",
     "prompt": "Hata olursa except bloğu çalışır. n nedir?",
     "options": [
      "9",
      "-2",
      "0",
      "-1",
      "-3",
      "1",
      "?!?"
     ],
     "answer": "-1"
    },
    {
     "code": "try:\n    n = int('7')\nexcept ValueError:\n    n = -1\nn",
     "prompt": "Hata olursa except bloğu çalışır. n nedir?",
     "options": [
      "7",
      "14",
      "9",
      "5",
      "6",
      "8",
      "?!?"
     ],
     "answer": "7"
    }
   ]
  },
  {
   "id": "hard_26",
   "title": "join ile Sayıları Birleştirme",
   "lesson": "str() ile çevirip join ile tek metin yapabilirsin.",
   "questions": [
    {
     "code": "'-'.join([str(i) for i in range(6)])",
     "prompt": "Sayıları metne çevirip birleştirir. Sonuç nedir?",
     "options": [
      "'0-1-2-3-4-'",
      "''",
      "'-1-2-3-4-5'",
      "'5-4-3-2-1-0'",
      "'0-1-2-3-4-50'",
      "'0-1-2-3-4-5'",
      "?!?"
     ],
     "answer": "'0-1-2-3-4-5'"
    },
    {
     "code": "''.join([str(i) for i in range(5)])",
     "prompt": "Sayıları metne çevirip birleştirir. Sonuç nedir?",
     "options": [
      "'43210'",
      "'012340'",
      "'0123'",
      "'01234'",
      "''",
      "'1234'",
      "?!?"
     ],
     "answer": "'01234'"
    },
    {
     "code": "''.join([str(i) for i in range(3)])",
     "prompt": "Sayıları metne çevirip birleştirir. Sonuç nedir?",
     "options": [
      "'01'",
      "'012'",
      "'0120'",
      "''",
      "'210'",
      "'12'",
      "?!?"
     ],
     "answer": "'012'"
    }
   ]
  },
  {
   "id": "hard_27",
   "title": "Sayıyı Ters Çevirme",
   "lesson": "while ile basamakları sondan alıp r * 10 + basamak diyerek ters sayı kurulur.",
   "questions": [
    {
     "code": "n = 710\nr = 0\nwhile n > 0:\n    r = r * 10 + n % 10\n    n //= 10\nr",
     "prompt": "Sayıyı ters çevirir. r nedir?",
     "options": [
      "34",
      "15",
      "16",
      "19",
      "18",
      "17",
      "?!?"
     ],
     "answer": "17"
    },
    {
     "code": "n = 784\nr = 0\nwhile n > 0:\n    r = r * 10 + n % 10\n    n //= 10\nr",
     "prompt": "Sayıyı ters çevirir. r nedir?",
     "options": [
      "486",
      "974",
      "485",
      "489",
      "488",
      "487",
      "?!?"
     ],
     "answer": "487"
    },
    {
     "code": "n = 768\nr = 0\nwhile n > 0:\n    r = r * 10 + n % 10\n    n //= 10\nr",
     "prompt": "Sayıyı ters çevirir. r nedir?",
     "options": [
      "1734",
      "865",
      "869",
      "867",
      "868",
      "866",
      "?!?"
     ],
     "answer": "867"
    }
   ]
  },
  {
   "id": "hard_28",
   "title": "Asal Sayı Algoritması",
   "lesson": "Bir sayı 2’den n-1’e kadar hiçbir sayıya bölünmüyorsa asaldır.",
   "questions": [
    {
     "code": "def asal(n):\n    if n < 2:\n        return False\n    for i in range(2, n):\n        if n % i == 0:\n            return False\n    return True\n\nasal(21)",
     "prompt": "Asal sayı 1 ve kendisinden başka böleni olmayandır. Sonuç nedir?",
     "options": [
      "0",
      "True",
      "'True'",
      "False",
      "None",
      "1",
      "?!?"
     ],
     "answer": "False"
    },
    {
     "code": "def asal(n):\n    if n < 2:\n        return False\n    for i in range(2, n):\n        if n % i == 0:\n            return False\n    return True\n\nasal(2)",
     "prompt": "Asal sayı 1 ve kendisinden başka böleni olmayandır. Sonuç nedir?",
     "options": [
      "0",
      "None",
      "1",
      "True",
      "'True'",
      "False",
      "?!?"
     ],
     "answer": "True"
    },
    {
     "code": "def asal(n):\n    if n < 2:\n        return False\n    for i in range(2, n):\n        if n % i == 0:\n            return False\n    return True\n\nasal(29)",
     "prompt": "Asal sayı 1 ve kendisinden başka böleni olmayandır. Sonuç nedir?",
     "options": [
      "False",
      "True",
      "1",
      "'True'",
      "None",
      "0",
      "?!?"
     ],
     "answer": "True"
    }
   ]
  },
  {
   "id": "hard_29",
   "title": "Ortalama Hesabı",
   "lesson": "Ortalama = toplam / eleman sayısı.",
   "questions": [
    {
     "code": "def ort(l):\n    return sum(l) / len(l)\n\nort([14, 6, 1, 7])",
     "prompt": "Ortalama = toplam / eleman sayısı. Sonuç nedir?",
     "options": [
      "6.0",
      "8.0",
      "7",
      "8",
      "7.0",
      "14.0",
      "?!?"
     ],
     "answer": "7.0"
    },
    {
     "code": "def ort(l):\n    return sum(l) / len(l)\n\nort([5, 10, 8, 1, 11])",
     "prompt": "Ortalama = toplam / eleman sayısı. Sonuç nedir?",
     "options": [
      "6.0",
      "8",
      "8.0",
      "7",
      "7.0",
      "14.0",
      "?!?"
     ],
     "answer": "7.0"
    },
    {
     "code": "def ort(l):\n    return sum(l) / len(l)\n\nort([15, 2, 4])",
     "prompt": "Ortalama = toplam / eleman sayısı. Sonuç nedir?",
     "options": [
      "8",
      "14.0",
      "8.0",
      "7.0",
      "7",
      "6.0",
      "?!?"
     ],
     "answer": "7.0"
    }
   ]
  },
  {
   "id": "hard_30",
   "title": "Listeyi Ters Çevirme",
   "lesson": "[::-1] veya reverse() ile liste tersine döner.",
   "questions": [
    {
     "code": "a = [15, 11, 36, 18, 35]\nb = a[::-1]\nb[0] + b[1]",
     "prompt": "Ters listenin ilk iki elemanı toplamı nedir?",
     "options": [
      "54",
      "51",
      "55",
      "53",
      "52",
      "106",
      "?!?"
     ],
     "answer": "53"
    },
    {
     "code": "a = [19, 29, 16, 17, 41]\na.reverse()\na[0] - a[-1]",
     "prompt": "reverse sonrası a[0] - a[-1] nedir?",
     "options": [
      "24",
      "23",
      "44",
      "22",
      "21",
      "20",
      "?!?"
     ],
     "answer": "22"
    },
    {
     "code": "a = [35, 39, 25, 23, 1]\na.reverse()\na[0] - a[-1]",
     "prompt": "reverse sonrası a[0] - a[-1] nedir?",
     "options": [
      "-33",
      "-68",
      "-32",
      "-34",
      "-35",
      "-36",
      "?!?"
     ],
     "answer": "-34"
    }
   ]
  },
  {
   "id": "hard_31",
   "title": "Kelime Sayma",
   "lesson": "split() kelimelere böler; len ve count ile sayılır.",
   "questions": [
    {
     "code": "len('bilgi merhaba elma kitap okul'.split())",
     "prompt": "Kaç kelime var?",
     "options": [
      "7",
      "4",
      "6",
      "3",
      "5",
      "10",
      "?!?"
     ],
     "answer": "5"
    },
    {
     "code": "len('masa robot python bilgi'.split())",
     "prompt": "Kaç kelime var?",
     "options": [
      "5",
      "8",
      "4",
      "3",
      "6",
      "2",
      "?!?"
     ],
     "answer": "4"
    },
    {
     "code": "'elma kitap elma merhaba'.split().count('elma')",
     "prompt": "“elma” kaç kez geçiyor?",
     "options": [
      "3",
      "4",
      "1",
      "2",
      "0",
      "12",
      "?!?"
     ],
     "answer": "2"
    }
   ]
  },
  {
   "id": "hard_32",
   "title": "Karma Problem 1",
   "lesson": "Fonksiyon, liste ve koşulu birleştir: çift sayıları ayıklayan fonksiyon.",
   "questions": [
    {
     "code": "def ciftler(l):\n    s = []\n    for x in l:\n        if x % 2 == 0:\n            s.append(x)\n    return s\n\nlen(ciftler([3, 8, 24, 14, 25]))",
     "prompt": "Fonksiyon çift sayıları toplar. len kaç döner?",
     "options": [
      "6",
      "1",
      "3",
      "4",
      "2",
      "5",
      "?!?"
     ],
     "answer": "3"
    },
    {
     "code": "def ciftler(l):\n    s = []\n    for x in l:\n        if x % 2 == 0:\n            s.append(x)\n    return s\n\nlen(ciftler([26, 3, 10, 29, 16]))",
     "prompt": "Fonksiyon çift sayıları toplar. len kaç döner?",
     "options": [
      "3",
      "1",
      "6",
      "2",
      "4",
      "5",
      "?!?"
     ],
     "answer": "3"
    },
    {
     "code": "def ciftler(l):\n    s = []\n    for x in l:\n        if x % 2 == 0:\n            s.append(x)\n    return s\n\nsum(ciftler([16, 11, 24, 19, 30]))",
     "prompt": "Çift sayıların toplamı nedir?",
     "options": [
      "69",
      "140",
      "70",
      "71",
      "72",
      "68",
      "?!?"
     ],
     "answer": "70"
    }
   ]
  },
  {
   "id": "hard_33",
   "title": "Karma Problem 2 (Final)",
   "lesson": "Bölenleri bulan fonksiyon: bir sayının tam böleni 0 kalan bırakır.",
   "questions": [
    {
     "code": "def f(n):\n    r = []\n    for i in range(1, n + 1):\n        if n % i == 0:\n            r.append(i)\n    return r\n\nf(12)",
     "prompt": "Fonksiyon bölenleri bulur. Sonuç nedir?",
     "options": [
      "[1, 2, 3, 4, 6]",
      "[1, 2, 3, 4, 6, 12]",
      "[1, 2, 3, 4, 6, 12, 1]",
      "[]",
      "[2, 3, 4, 6, 12]",
      "[12, 6, 4, 3, 2, 1]",
      "?!?"
     ],
     "answer": "[1, 2, 3, 4, 6, 12]"
    },
    {
     "code": "def f(n):\n    r = []\n    for i in range(1, n + 1):\n        if n % i == 0:\n            r.append(i)\n    return r\n\nlen(f(8))",
     "prompt": "Kaç bölen bulunur?",
     "options": [
      "8",
      "2",
      "6",
      "4",
      "5",
      "3",
      "?!?"
     ],
     "answer": "4"
    },
    {
     "code": "def f(n):\n    r = []\n    for i in range(1, n + 1):\n        if n % i == 0:\n            r.append(i)\n    return r\n\nf(9)",
     "prompt": "Fonksiyon bölenleri bulur. Sonuç nedir?",
     "options": [
      "[9, 3, 1]",
      "[]",
      "[1, 3]",
      "[1, 3, 9]",
      "[1, 3, 9, 1]",
      "[3, 9]",
      "?!?"
     ],
     "answer": "[1, 3, 9]"
    }
   ]
  }
 ]
};
