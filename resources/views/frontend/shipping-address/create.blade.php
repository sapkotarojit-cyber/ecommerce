@extends('frontend.frontend-layout')

@section('title', 'Add New Address - Empireinnovation')
<title>Add New Address - EmpireInnovation</title>

@section('content')
<section class="py-8 md:py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8">
            <div class="flex items-center space-x-3 mb-6">
                <a href="{{ route('shipping-address.index') }}" class="text-[#1a2a6c] hover:text-[#c9a84c] transition-colors">
                    <i class="fas fa-arrow-left text-lg"></i>
                </a>
                <h1 class="text-2xl font-bold text-[#1a2a6c]">Add New Address</h1>
            </div>
            
            <form action="{{ route('shipping-address.store') }}" method="POST" class="space-y-5">
                @csrf
                
                <!-- Recipient's Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Recipient’s Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent transition-all"
                           placeholder="Input the real name" required>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Phone Number -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent transition-all"
                           placeholder="Please input Phone Number" required>
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Region/City/District -->
                <div>
                    <label for="region" class="block text-sm font-medium text-gray-700 mb-1">Region/City/District <span class="text-red-500">*</span></label>
                    <select id="region" name="region" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent transition-all">
                        <option value="">Select your city or district</option>
                        <option value="kathmandu" {{ old('region', '') === 'kathmandu' ? 'selected' : '' }}>Kathmandu</option>
                        <option value="kathmandu valley" {{ old('region', '') === 'kathmandu valley' ? 'selected' : '' }}>Kathmandu Valley</option>
                        <option value="ktm" {{ old('region', '') === 'ktm' ? 'selected' : '' }}>Ktm</option>
                        <option value="lalitpur" {{ old('region', '') === 'lalitpur' ? 'selected' : '' }}>Lalitpur</option>
                        <option value="patan" {{ old('region', '') === 'patan' ? 'selected' : '' }}>Patan</option>
                        <option value="bhaktapur" {{ old('region', '') === 'bhaktapur' ? 'selected' : '' }}>Bhaktapur</option>
                        <option value="bhatgaon" {{ old('region', '') === 'bhatgaon' ? 'selected' : '' }}>Bhatgaon</option>
                        <option value="kirtipur" {{ old('region', '') === 'kirtipur' ? 'selected' : '' }}>Kirtipur</option>
                        <option value="madhyapur thimi" {{ old('region', '') === 'madhyapur thimi' ? 'selected' : '' }}>Madhyapur Thimi</option>
                        <option value="thimi" {{ old('region', '') === 'thimi' ? 'selected' : '' }}>Thimi</option>
                        <option value="tokha" {{ old('region', '') === 'tokha' ? 'selected' : '' }}>Tokha</option>
                        <option value="budhanilkantha" {{ old('region', '') === 'budhanilkantha' ? 'selected' : '' }}>Budhanilkantha</option>
                        <option value="tarakeshwar" {{ old('region', '') === 'tarakeshwar' ? 'selected' : '' }}>Tarakeshwar</option>
                        <option value="chandragiri" {{ old('region', '') === 'chandragiri' ? 'selected' : '' }}>Chandragiri</option>
                        <option value="dakshinkali" {{ old('region', '') === 'dakshinkali' ? 'selected' : '' }}>Dakshinkali</option>
                        <option value="shankharapur" {{ old('region', '') === 'shankharapur' ? 'selected' : '' }}>Shankharapur</option>
                        <option value="gokarneshwor" {{ old('region', '') === 'gokarneshwor' ? 'selected' : '' }}>Gokarneshwor</option>
                        <option value="kageshwori manohara" {{ old('region', '') === 'kageshwori manohara' ? 'selected' : '' }}>Kageshwori Manohara</option>
                        <option value="nagarjun" {{ old('region', '') === 'nagarjun' ? 'selected' : '' }}>Nagarjun</option>
                        <option value="changunarayan" {{ old('region', '') === 'changunarayan' ? 'selected' : '' }}>Changunarayan</option>
                        <option value="suryabinayak" {{ old('region', '') === 'suryabinayak' ? 'selected' : '' }}>Suryabinayak</option>
                        <option value="mahalaxmi" {{ old('region', '') === 'mahalaxmi' ? 'selected' : '' }}>Mahalaxmi</option>
                        <option value="godawari" {{ old('region', '') === 'godawari' ? 'selected' : '' }}>Godawari</option>
                        <option value="chitwan" {{ old('region', '') === 'chitwan' ? 'selected' : '' }}>Chitwan</option>
                        <option value="bharatpur" {{ old('region', '') === 'bharatpur' ? 'selected' : '' }}>Bharatpur</option>
                        <option value="makwanpur" {{ old('region', '') === 'makwanpur' ? 'selected' : '' }}>Makwanpur</option>
                        <option value="hetauda" {{ old('region', '') === 'hetauda' ? 'selected' : '' }}>Hetauda</option>
                        <option value="kavrepalanchok" {{ old('region', '') === 'kavrepalanchok' ? 'selected' : '' }}>Kavrepalanchok</option>
                        <option value="kavre" {{ old('region', '') === 'kavre' ? 'selected' : '' }}>Kavre</option>
                        <option value="dhulikhel" {{ old('region', '') === 'dhulikhel' ? 'selected' : '' }}>Dhulikhel</option>
                        <option value="panauti" {{ old('region', '') === 'panauti' ? 'selected' : '' }}>Panauti</option>
                        <option value="sindhupalchok" {{ old('region', '') === 'sindhupalchok' ? 'selected' : '' }}>Sindhupalchok</option>
                        <option value="chautara" {{ old('region', '') === 'chautara' ? 'selected' : '' }}>Chautara</option>
                        <option value="dolakha" {{ old('region', '') === 'dolakha' ? 'selected' : '' }}>Dolakha</option>
                        <option value="charikot" {{ old('region', '') === 'charikot' ? 'selected' : '' }}>Charikot</option>
                        <option value="ramechhap" {{ old('region', '') === 'ramechhap' ? 'selected' : '' }}>Ramechhap</option>
                        <option value="manthali" {{ old('region', '') === 'manthali' ? 'selected' : '' }}>Manthali</option>
                        <option value="sindhuli" {{ old('region', '') === 'sindhuli' ? 'selected' : '' }}>Sindhuli</option>
                        <option value="kamalamai" {{ old('region', '') === 'kamalamai' ? 'selected' : '' }}>Kamalamai</option>
                        <option value="rasuwa" {{ old('region', '') === 'rasuwa' ? 'selected' : '' }}>Rasuwa</option>
                        <option value="dhunche" {{ old('region', '') === 'dhunche' ? 'selected' : '' }}>Dhunche</option>
                        <option value="nuwakot" {{ old('region', '') === 'nuwakot' ? 'selected' : '' }}>Nuwakot</option>
                        <option value="bidur" {{ old('region', '') === 'bidur' ? 'selected' : '' }}>Bidur</option>
                        <option value="dhading" {{ old('region', '') === 'dhading' ? 'selected' : '' }}>Dhading</option>
                        <option value="dhading besi" {{ old('region', '') === 'dhading besi' ? 'selected' : '' }}>Dhading Besi</option>
                        <option value="morang" {{ old('region', '') === 'morang' ? 'selected' : '' }}>Morang</option>
                        <option value="sunsari" {{ old('region', '') === 'sunsari' ? 'selected' : '' }}>Sunsari</option>
                        <option value="jhapa" {{ old('region', '') === 'jhapa' ? 'selected' : '' }}>Jhapa</option>
                        <option value="ilam" {{ old('region', '') === 'ilam' ? 'selected' : '' }}>Ilam</option>
                        <option value="panchthar" {{ old('region', '') === 'panchthar' ? 'selected' : '' }}>Panchthar</option>
                        <option value="taplejung" {{ old('region', '') === 'taplejung' ? 'selected' : '' }}>Taplejung</option>
                        <option value="dhankuta" {{ old('region', '') === 'dhankuta' ? 'selected' : '' }}>Dhankuta</option>
                        <option value="terhathum" {{ old('region', '') === 'terhathum' ? 'selected' : '' }}>Terhathum</option>
                        <option value="tehrathum" {{ old('region', '') === 'tehrathum' ? 'selected' : '' }}>Tehrathum</option>
                        <option value="sankhuwasabha" {{ old('region', '') === 'sankhuwasabha' ? 'selected' : '' }}>Sankhuwasabha</option>
                        <option value="bhojpur" {{ old('region', '') === 'bhojpur' ? 'selected' : '' }}>Bhojpur</option>
                        <option value="solukhumbu" {{ old('region', '') === 'solukhumbu' ? 'selected' : '' }}>Solukhumbu</option>
                        <option value="okhaldhunga" {{ old('region', '') === 'okhaldhunga' ? 'selected' : '' }}>Okhaldhunga</option>
                        <option value="khotang" {{ old('region', '') === 'khotang' ? 'selected' : '' }}>Khotang</option>
                        <option value="udayapur" {{ old('region', '') === 'udayapur' ? 'selected' : '' }}>Udayapur</option>
                        <option value="saptari" {{ old('region', '') === 'saptari' ? 'selected' : '' }}>Saptari</option>
                        <option value="siraha" {{ old('region', '') === 'siraha' ? 'selected' : '' }}>Siraha</option>
                        <option value="dhanusha" {{ old('region', '') === 'dhanusha' ? 'selected' : '' }}>Dhanusha</option>
                        <option value="mahottari" {{ old('region', '') === 'mahottari' ? 'selected' : '' }}>Mahottari</option>
                        <option value="sarlahi" {{ old('region', '') === 'sarlahi' ? 'selected' : '' }}>Sarlahi</option>
                        <option value="rautahat" {{ old('region', '') === 'rautahat' ? 'selected' : '' }}>Rautahat</option>
                        <option value="bara" {{ old('region', '') === 'bara' ? 'selected' : '' }}>Bara</option>
                        <option value="parsa" {{ old('region', '') === 'parsa' ? 'selected' : '' }}>Parsa</option>
                        <option value="gorkha" {{ old('region', '') === 'gorkha' ? 'selected' : '' }}>Gorkha</option>
                        <option value="lamjung" {{ old('region', '') === 'lamjung' ? 'selected' : '' }}>Lamjung</option>
                        <option value="tanahun" {{ old('region', '') === 'tanahun' ? 'selected' : '' }}>Tanahun</option>
                        <option value="kaski" {{ old('region', '') === 'kaski' ? 'selected' : '' }}>Kaski</option>
                        <option value="pokhara" {{ old('region', '') === 'pokhara' ? 'selected' : '' }}>Pokhara</option>
                        <option value="manang" {{ old('region', '') === 'manang' ? 'selected' : '' }}>Manang</option>
                        <option value="mustang" {{ old('region', '') === 'mustang' ? 'selected' : '' }}>Mustang</option>
                        <option value="parbat" {{ old('region', '') === 'parbat' ? 'selected' : '' }}>Parbat</option>
                        <option value="syangja" {{ old('region', '') === 'syangja' ? 'selected' : '' }}>Syangja</option>
                        <option value="myagdi" {{ old('region', '') === 'myagdi' ? 'selected' : '' }}>Myagdi</option>
                        <option value="baglung" {{ old('region', '') === 'baglung' ? 'selected' : '' }}>Baglung</option>
                        <option value="nawalpur" {{ old('region', '') === 'nawalpur' ? 'selected' : '' }}>Nawalpur</option>
                        <option value="east nawalparasi" {{ old('region', '') === 'east nawalparasi' ? 'selected' : '' }}>East Nawalparasi</option>
                        <option value="nawalparasi east" {{ old('region', '') === 'nawalparasi east' ? 'selected' : '' }}>Nawalparasi East</option>
                        <option value="parasi" {{ old('region', '') === 'parasi' ? 'selected' : '' }}>Parasi</option>
                        <option value="west nawalparasi" {{ old('region', '') === 'west nawalparasi' ? 'selected' : '' }}>West Nawalparasi</option>
                        <option value="nawalparasi west" {{ old('region', '') === 'nawalparasi west' ? 'selected' : '' }}>Nawalparasi West</option>
                        <option value="palpa" {{ old('region', '') === 'palpa' ? 'selected' : '' }}>Palpa</option>
                        <option value="gulmi" {{ old('region', '') === 'gulmi' ? 'selected' : '' }}>Gulmi</option>
                        <option value="arghakhanchi" {{ old('region', '') === 'arghakhanchi' ? 'selected' : '' }}>Arghakhanchi</option>
                        <option value="kapilvastu" {{ old('region', '') === 'kapilvastu' ? 'selected' : '' }}>Kapilvastu</option>
                        <option value="rupandehi" {{ old('region', '') === 'rupandehi' ? 'selected' : '' }}>Rupandehi</option>
                        <option value="butwal" {{ old('region', '') === 'butwal' ? 'selected' : '' }}>Butwal</option>
                        <option value="dang" {{ old('region', '') === 'dang' ? 'selected' : '' }}>Dang</option>
                        <option value="pyuthan" {{ old('region', '') === 'pyuthan' ? 'selected' : '' }}>Pyuthan</option>
                        <option value="rolpa" {{ old('region', '') === 'rolpa' ? 'selected' : '' }}>Rolpa</option>
                        <option value="rukum east" {{ old('region', '') === 'rukum east' ? 'selected' : '' }}>Rukum East</option>
                        <option value="eastern rukum" {{ old('region', '') === 'eastern rukum' ? 'selected' : '' }}>Eastern Rukum</option>
                        <option value="rukum west" {{ old('region', '') === 'rukum west' ? 'selected' : '' }}>Rukum West</option>
                        <option value="western rukum" {{ old('region', '') === 'western rukum' ? 'selected' : '' }}>Western Rukum</option>
                        <option value="salyan" {{ old('region', '') === 'salyan' ? 'selected' : '' }}>Salyan</option>
                        <option value="banke" {{ old('region', '') === 'banke' ? 'selected' : '' }}>Banke</option>
                        <option value="bardiya" {{ old('region', '') === 'bardiya' ? 'selected' : '' }}>Bardiya</option>
                        <option value="surkhet" {{ old('region', '') === 'surkhet' ? 'selected' : '' }}>Surkhet</option>
                        <option value="dailekh" {{ old('region', '') === 'dailekh' ? 'selected' : '' }}>Dailekh</option>
                        <option value="jajarkot" {{ old('region', '') === 'jajarkot' ? 'selected' : '' }}>Jajarkot</option>
                        <option value="dolpa" {{ old('region', '') === 'dolpa' ? 'selected' : '' }}>Dolpa</option>
                        <option value="jumla" {{ old('region', '') === 'jumla' ? 'selected' : '' }}>Jumla</option>
                        <option value="kalikot" {{ old('region', '') === 'kalikot' ? 'selected' : '' }}>Kalikot</option>
                        <option value="mugu" {{ old('region', '') === 'mugu' ? 'selected' : '' }}>Mugu</option>
                        <option value="humla" {{ old('region', '') === 'humla' ? 'selected' : '' }}>Humla</option>
                        <option value="achham" {{ old('region', '') === 'achham' ? 'selected' : '' }}>Achham</option>
                        <option value="doti" {{ old('region', '') === 'doti' ? 'selected' : '' }}>Doti</option>
                        <option value="kailali" {{ old('region', '') === 'kailali' ? 'selected' : '' }}>Kailali</option>
                        <option value="kanchanpur" {{ old('region', '') === 'kanchanpur' ? 'selected' : '' }}>Kanchanpur</option>
                        <option value="dadeldhura" {{ old('region', '') === 'dadeldhura' ? 'selected' : '' }}>Dadeldhura</option>
                        <option value="baitadi" {{ old('region', '') === 'baitadi' ? 'selected' : '' }}>Baitadi</option>
                        <option value="darchula" {{ old('region', '') === 'darchula' ? 'selected' : '' }}>Darchula</option>
                        <option value="bajhang" {{ old('region', '') === 'bajhang' ? 'selected' : '' }}>Bajhang</option>
                        <option value="bajura" {{ old('region', '') === 'bajura' ? 'selected' : '' }}>Bajura</option>
                        <option value="Bagmati" {{ old('region', '') === 'Bagmati' ? 'selected' : '' }}>Bagmati</option>
                        <option value="Koshi" {{ old('region', '') === 'Koshi' ? 'selected' : '' }}>Koshi</option>
                        <option value="Madhesh" {{ old('region', '') === 'Madhesh' ? 'selected' : '' }}>Madhesh</option>
                        <option value="Gandaki" {{ old('region', '') === 'Gandaki' ? 'selected' : '' }}>Gandaki</option>
                        <option value="Lumbini" {{ old('region', '') === 'Lumbini' ? 'selected' : '' }}>Lumbini</option>
                        <option value="Karnali" {{ old('region', '') === 'Karnali' ? 'selected' : '' }}>Karnali</option>
                        <option value="Sudurpashchim" {{ old('region', '') === 'Sudurpashchim' ? 'selected' : '' }}>Sudurpashchim</option>
                    </select>
                    @error('region')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Address -->
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address <span class="text-red-500">*</span></label>
                    <textarea id="address" name="address" rows="3" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent transition-all"
                              placeholder="House no./building/street/area" required>{{ old('address') }}</textarea>
                    @error('address')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Landmark (Optional) -->
                <div>
                    <label for="landmark" class="block text-sm font-medium text-gray-700 mb-1">Landmark (Optional)</label>
                    <input type="text" id="landmark" name="landmark" value="{{ old('landmark') }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent transition-all"
                           placeholder="Add Additional Info">
                    @error('landmark')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address Category -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Address Category</label>
                    <div class="flex items-center space-x-6">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="radio" name="address_type" value="Home" {{ old('address_type', 'Home') == 'Home' ? 'checked' : '' }} class="text-[#c9a84c] focus:ring-[#c9a84c]">
                            <span class="text-sm text-gray-700">Home</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="radio" name="address_type" value="Office" {{ old('address_type') == 'Office' ? 'checked' : '' }} class="text-[#c9a84c] focus:ring-[#c9a84c]">
                            <span class="text-sm text-gray-700">Office</span>
                        </label>
                    </div>
                </div>
                
                <!-- Default Toggles -->
                <div class="space-y-3 pt-2 border-t border-gray-100">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Default Shipping Address</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_default_shipping" value="1" class="sr-only peer" {{ old('is_default_shipping') ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#c9a84c]"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Default Billing Address</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_default_billing" value="1" class="sr-only peer" {{ old('is_default_billing') ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#c9a84c]"></div>
                        </label>
                    </div>
                </div>
                
                <div class="pt-4">
                    <button type="submit" class="w-full px-6 py-3.5 bg-[#f97316] text-white font-bold rounded-lg hover:bg-[#ea580c] transition-all shadow-md">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection