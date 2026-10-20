@extends('frontend.frontend-layout')

@section('title', 'Edit Address - Empireinnovation')
<title>Edit Address - EmpireInnovation</title>

@section('content')
<section class="py-8 md:py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8">
            <div class="flex items-center space-x-3 mb-6">
                <a href="{{ route('shipping-address.index') }}" class="text-[#1a2a6c] hover:text-[#c9a84c] transition-colors">
                    <i class="fas fa-arrow-left text-lg"></i>
                </a>
                <h1 class="text-2xl font-bold text-[#1a2a6c]">Edit Address</h1>
            </div>
            
            <form action="{{ route('shipping-address.update', $address) }}" method="POST" class="space-y-5">
                @csrf
                @method('PATCH')
                
                <!-- Recipient's Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Recipient’s Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $address->name) }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent transition-all"
                           placeholder="Input the real name" required>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Phone Number -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $address->phone) }}" 
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
                        <option value="kathmandu" {{ old('region', $address->region ?? '') === 'kathmandu' ? 'selected' : '' }}>Kathmandu</option>
                        <option value="kathmandu valley" {{ old('region', $address->region ?? '') === 'kathmandu valley' ? 'selected' : '' }}>Kathmandu Valley</option>
                        <option value="ktm" {{ old('region', $address->region ?? '') === 'ktm' ? 'selected' : '' }}>Ktm</option>
                        <option value="lalitpur" {{ old('region', $address->region ?? '') === 'lalitpur' ? 'selected' : '' }}>Lalitpur</option>
                        <option value="patan" {{ old('region', $address->region ?? '') === 'patan' ? 'selected' : '' }}>Patan</option>
                        <option value="bhaktapur" {{ old('region', $address->region ?? '') === 'bhaktapur' ? 'selected' : '' }}>Bhaktapur</option>
                        <option value="bhatgaon" {{ old('region', $address->region ?? '') === 'bhatgaon' ? 'selected' : '' }}>Bhatgaon</option>
                        <option value="kirtipur" {{ old('region', $address->region ?? '') === 'kirtipur' ? 'selected' : '' }}>Kirtipur</option>
                        <option value="madhyapur thimi" {{ old('region', $address->region ?? '') === 'madhyapur thimi' ? 'selected' : '' }}>Madhyapur Thimi</option>
                        <option value="thimi" {{ old('region', $address->region ?? '') === 'thimi' ? 'selected' : '' }}>Thimi</option>
                        <option value="tokha" {{ old('region', $address->region ?? '') === 'tokha' ? 'selected' : '' }}>Tokha</option>
                        <option value="budhanilkantha" {{ old('region', $address->region ?? '') === 'budhanilkantha' ? 'selected' : '' }}>Budhanilkantha</option>
                        <option value="tarakeshwar" {{ old('region', $address->region ?? '') === 'tarakeshwar' ? 'selected' : '' }}>Tarakeshwar</option>
                        <option value="chandragiri" {{ old('region', $address->region ?? '') === 'chandragiri' ? 'selected' : '' }}>Chandragiri</option>
                        <option value="dakshinkali" {{ old('region', $address->region ?? '') === 'dakshinkali' ? 'selected' : '' }}>Dakshinkali</option>
                        <option value="shankharapur" {{ old('region', $address->region ?? '') === 'shankharapur' ? 'selected' : '' }}>Shankharapur</option>
                        <option value="gokarneshwor" {{ old('region', $address->region ?? '') === 'gokarneshwor' ? 'selected' : '' }}>Gokarneshwor</option>
                        <option value="kageshwori manohara" {{ old('region', $address->region ?? '') === 'kageshwori manohara' ? 'selected' : '' }}>Kageshwori Manohara</option>
                        <option value="nagarjun" {{ old('region', $address->region ?? '') === 'nagarjun' ? 'selected' : '' }}>Nagarjun</option>
                        <option value="changunarayan" {{ old('region', $address->region ?? '') === 'changunarayan' ? 'selected' : '' }}>Changunarayan</option>
                        <option value="suryabinayak" {{ old('region', $address->region ?? '') === 'suryabinayak' ? 'selected' : '' }}>Suryabinayak</option>
                        <option value="mahalaxmi" {{ old('region', $address->region ?? '') === 'mahalaxmi' ? 'selected' : '' }}>Mahalaxmi</option>
                        <option value="godawari" {{ old('region', $address->region ?? '') === 'godawari' ? 'selected' : '' }}>Godawari</option>
                        <option value="chitwan" {{ old('region', $address->region ?? '') === 'chitwan' ? 'selected' : '' }}>Chitwan</option>
                        <option value="bharatpur" {{ old('region', $address->region ?? '') === 'bharatpur' ? 'selected' : '' }}>Bharatpur</option>
                        <option value="makwanpur" {{ old('region', $address->region ?? '') === 'makwanpur' ? 'selected' : '' }}>Makwanpur</option>
                        <option value="hetauda" {{ old('region', $address->region ?? '') === 'hetauda' ? 'selected' : '' }}>Hetauda</option>
                        <option value="kavrepalanchok" {{ old('region', $address->region ?? '') === 'kavrepalanchok' ? 'selected' : '' }}>Kavrepalanchok</option>
                        <option value="kavre" {{ old('region', $address->region ?? '') === 'kavre' ? 'selected' : '' }}>Kavre</option>
                        <option value="dhulikhel" {{ old('region', $address->region ?? '') === 'dhulikhel' ? 'selected' : '' }}>Dhulikhel</option>
                        <option value="panauti" {{ old('region', $address->region ?? '') === 'panauti' ? 'selected' : '' }}>Panauti</option>
                        <option value="sindhupalchok" {{ old('region', $address->region ?? '') === 'sindhupalchok' ? 'selected' : '' }}>Sindhupalchok</option>
                        <option value="chautara" {{ old('region', $address->region ?? '') === 'chautara' ? 'selected' : '' }}>Chautara</option>
                        <option value="dolakha" {{ old('region', $address->region ?? '') === 'dolakha' ? 'selected' : '' }}>Dolakha</option>
                        <option value="charikot" {{ old('region', $address->region ?? '') === 'charikot' ? 'selected' : '' }}>Charikot</option>
                        <option value="ramechhap" {{ old('region', $address->region ?? '') === 'ramechhap' ? 'selected' : '' }}>Ramechhap</option>
                        <option value="manthali" {{ old('region', $address->region ?? '') === 'manthali' ? 'selected' : '' }}>Manthali</option>
                        <option value="sindhuli" {{ old('region', $address->region ?? '') === 'sindhuli' ? 'selected' : '' }}>Sindhuli</option>
                        <option value="kamalamai" {{ old('region', $address->region ?? '') === 'kamalamai' ? 'selected' : '' }}>Kamalamai</option>
                        <option value="rasuwa" {{ old('region', $address->region ?? '') === 'rasuwa' ? 'selected' : '' }}>Rasuwa</option>
                        <option value="dhunche" {{ old('region', $address->region ?? '') === 'dhunche' ? 'selected' : '' }}>Dhunche</option>
                        <option value="nuwakot" {{ old('region', $address->region ?? '') === 'nuwakot' ? 'selected' : '' }}>Nuwakot</option>
                        <option value="bidur" {{ old('region', $address->region ?? '') === 'bidur' ? 'selected' : '' }}>Bidur</option>
                        <option value="dhading" {{ old('region', $address->region ?? '') === 'dhading' ? 'selected' : '' }}>Dhading</option>
                        <option value="dhading besi" {{ old('region', $address->region ?? '') === 'dhading besi' ? 'selected' : '' }}>Dhading Besi</option>
                        <option value="morang" {{ old('region', $address->region ?? '') === 'morang' ? 'selected' : '' }}>Morang</option>
                        <option value="sunsari" {{ old('region', $address->region ?? '') === 'sunsari' ? 'selected' : '' }}>Sunsari</option>
                        <option value="jhapa" {{ old('region', $address->region ?? '') === 'jhapa' ? 'selected' : '' }}>Jhapa</option>
                        <option value="ilam" {{ old('region', $address->region ?? '') === 'ilam' ? 'selected' : '' }}>Ilam</option>
                        <option value="panchthar" {{ old('region', $address->region ?? '') === 'panchthar' ? 'selected' : '' }}>Panchthar</option>
                        <option value="taplejung" {{ old('region', $address->region ?? '') === 'taplejung' ? 'selected' : '' }}>Taplejung</option>
                        <option value="dhankuta" {{ old('region', $address->region ?? '') === 'dhankuta' ? 'selected' : '' }}>Dhankuta</option>
                        <option value="terhathum" {{ old('region', $address->region ?? '') === 'terhathum' ? 'selected' : '' }}>Terhathum</option>
                        <option value="tehrathum" {{ old('region', $address->region ?? '') === 'tehrathum' ? 'selected' : '' }}>Tehrathum</option>
                        <option value="sankhuwasabha" {{ old('region', $address->region ?? '') === 'sankhuwasabha' ? 'selected' : '' }}>Sankhuwasabha</option>
                        <option value="bhojpur" {{ old('region', $address->region ?? '') === 'bhojpur' ? 'selected' : '' }}>Bhojpur</option>
                        <option value="solukhumbu" {{ old('region', $address->region ?? '') === 'solukhumbu' ? 'selected' : '' }}>Solukhumbu</option>
                        <option value="okhaldhunga" {{ old('region', $address->region ?? '') === 'okhaldhunga' ? 'selected' : '' }}>Okhaldhunga</option>
                        <option value="khotang" {{ old('region', $address->region ?? '') === 'khotang' ? 'selected' : '' }}>Khotang</option>
                        <option value="udayapur" {{ old('region', $address->region ?? '') === 'udayapur' ? 'selected' : '' }}>Udayapur</option>
                        <option value="saptari" {{ old('region', $address->region ?? '') === 'saptari' ? 'selected' : '' }}>Saptari</option>
                        <option value="siraha" {{ old('region', $address->region ?? '') === 'siraha' ? 'selected' : '' }}>Siraha</option>
                        <option value="dhanusha" {{ old('region', $address->region ?? '') === 'dhanusha' ? 'selected' : '' }}>Dhanusha</option>
                        <option value="mahottari" {{ old('region', $address->region ?? '') === 'mahottari' ? 'selected' : '' }}>Mahottari</option>
                        <option value="sarlahi" {{ old('region', $address->region ?? '') === 'sarlahi' ? 'selected' : '' }}>Sarlahi</option>
                        <option value="rautahat" {{ old('region', $address->region ?? '') === 'rautahat' ? 'selected' : '' }}>Rautahat</option>
                        <option value="bara" {{ old('region', $address->region ?? '') === 'bara' ? 'selected' : '' }}>Bara</option>
                        <option value="parsa" {{ old('region', $address->region ?? '') === 'parsa' ? 'selected' : '' }}>Parsa</option>
                        <option value="gorkha" {{ old('region', $address->region ?? '') === 'gorkha' ? 'selected' : '' }}>Gorkha</option>
                        <option value="lamjung" {{ old('region', $address->region ?? '') === 'lamjung' ? 'selected' : '' }}>Lamjung</option>
                        <option value="tanahun" {{ old('region', $address->region ?? '') === 'tanahun' ? 'selected' : '' }}>Tanahun</option>
                        <option value="kaski" {{ old('region', $address->region ?? '') === 'kaski' ? 'selected' : '' }}>Kaski</option>
                        <option value="pokhara" {{ old('region', $address->region ?? '') === 'pokhara' ? 'selected' : '' }}>Pokhara</option>
                        <option value="manang" {{ old('region', $address->region ?? '') === 'manang' ? 'selected' : '' }}>Manang</option>
                        <option value="mustang" {{ old('region', $address->region ?? '') === 'mustang' ? 'selected' : '' }}>Mustang</option>
                        <option value="parbat" {{ old('region', $address->region ?? '') === 'parbat' ? 'selected' : '' }}>Parbat</option>
                        <option value="syangja" {{ old('region', $address->region ?? '') === 'syangja' ? 'selected' : '' }}>Syangja</option>
                        <option value="myagdi" {{ old('region', $address->region ?? '') === 'myagdi' ? 'selected' : '' }}>Myagdi</option>
                        <option value="baglung" {{ old('region', $address->region ?? '') === 'baglung' ? 'selected' : '' }}>Baglung</option>
                        <option value="nawalpur" {{ old('region', $address->region ?? '') === 'nawalpur' ? 'selected' : '' }}>Nawalpur</option>
                        <option value="east nawalparasi" {{ old('region', $address->region ?? '') === 'east nawalparasi' ? 'selected' : '' }}>East Nawalparasi</option>
                        <option value="nawalparasi east" {{ old('region', $address->region ?? '') === 'nawalparasi east' ? 'selected' : '' }}>Nawalparasi East</option>
                        <option value="parasi" {{ old('region', $address->region ?? '') === 'parasi' ? 'selected' : '' }}>Parasi</option>
                        <option value="west nawalparasi" {{ old('region', $address->region ?? '') === 'west nawalparasi' ? 'selected' : '' }}>West Nawalparasi</option>
                        <option value="nawalparasi west" {{ old('region', $address->region ?? '') === 'nawalparasi west' ? 'selected' : '' }}>Nawalparasi West</option>
                        <option value="palpa" {{ old('region', $address->region ?? '') === 'palpa' ? 'selected' : '' }}>Palpa</option>
                        <option value="gulmi" {{ old('region', $address->region ?? '') === 'gulmi' ? 'selected' : '' }}>Gulmi</option>
                        <option value="arghakhanchi" {{ old('region', $address->region ?? '') === 'arghakhanchi' ? 'selected' : '' }}>Arghakhanchi</option>
                        <option value="kapilvastu" {{ old('region', $address->region ?? '') === 'kapilvastu' ? 'selected' : '' }}>Kapilvastu</option>
                        <option value="rupandehi" {{ old('region', $address->region ?? '') === 'rupandehi' ? 'selected' : '' }}>Rupandehi</option>
                        <option value="butwal" {{ old('region', $address->region ?? '') === 'butwal' ? 'selected' : '' }}>Butwal</option>
                        <option value="dang" {{ old('region', $address->region ?? '') === 'dang' ? 'selected' : '' }}>Dang</option>
                        <option value="pyuthan" {{ old('region', $address->region ?? '') === 'pyuthan' ? 'selected' : '' }}>Pyuthan</option>
                        <option value="rolpa" {{ old('region', $address->region ?? '') === 'rolpa' ? 'selected' : '' }}>Rolpa</option>
                        <option value="rukum east" {{ old('region', $address->region ?? '') === 'rukum east' ? 'selected' : '' }}>Rukum East</option>
                        <option value="eastern rukum" {{ old('region', $address->region ?? '') === 'eastern rukum' ? 'selected' : '' }}>Eastern Rukum</option>
                        <option value="rukum west" {{ old('region', $address->region ?? '') === 'rukum west' ? 'selected' : '' }}>Rukum West</option>
                        <option value="western rukum" {{ old('region', $address->region ?? '') === 'western rukum' ? 'selected' : '' }}>Western Rukum</option>
                        <option value="salyan" {{ old('region', $address->region ?? '') === 'salyan' ? 'selected' : '' }}>Salyan</option>
                        <option value="banke" {{ old('region', $address->region ?? '') === 'banke' ? 'selected' : '' }}>Banke</option>
                        <option value="bardiya" {{ old('region', $address->region ?? '') === 'bardiya' ? 'selected' : '' }}>Bardiya</option>
                        <option value="surkhet" {{ old('region', $address->region ?? '') === 'surkhet' ? 'selected' : '' }}>Surkhet</option>
                        <option value="dailekh" {{ old('region', $address->region ?? '') === 'dailekh' ? 'selected' : '' }}>Dailekh</option>
                        <option value="jajarkot" {{ old('region', $address->region ?? '') === 'jajarkot' ? 'selected' : '' }}>Jajarkot</option>
                        <option value="dolpa" {{ old('region', $address->region ?? '') === 'dolpa' ? 'selected' : '' }}>Dolpa</option>
                        <option value="jumla" {{ old('region', $address->region ?? '') === 'jumla' ? 'selected' : '' }}>Jumla</option>
                        <option value="kalikot" {{ old('region', $address->region ?? '') === 'kalikot' ? 'selected' : '' }}>Kalikot</option>
                        <option value="mugu" {{ old('region', $address->region ?? '') === 'mugu' ? 'selected' : '' }}>Mugu</option>
                        <option value="humla" {{ old('region', $address->region ?? '') === 'humla' ? 'selected' : '' }}>Humla</option>
                        <option value="achham" {{ old('region', $address->region ?? '') === 'achham' ? 'selected' : '' }}>Achham</option>
                        <option value="doti" {{ old('region', $address->region ?? '') === 'doti' ? 'selected' : '' }}>Doti</option>
                        <option value="kailali" {{ old('region', $address->region ?? '') === 'kailali' ? 'selected' : '' }}>Kailali</option>
                        <option value="kanchanpur" {{ old('region', $address->region ?? '') === 'kanchanpur' ? 'selected' : '' }}>Kanchanpur</option>
                        <option value="dadeldhura" {{ old('region', $address->region ?? '') === 'dadeldhura' ? 'selected' : '' }}>Dadeldhura</option>
                        <option value="baitadi" {{ old('region', $address->region ?? '') === 'baitadi' ? 'selected' : '' }}>Baitadi</option>
                        <option value="darchula" {{ old('region', $address->region ?? '') === 'darchula' ? 'selected' : '' }}>Darchula</option>
                        <option value="bajhang" {{ old('region', $address->region ?? '') === 'bajhang' ? 'selected' : '' }}>Bajhang</option>
                        <option value="bajura" {{ old('region', $address->region ?? '') === 'bajura' ? 'selected' : '' }}>Bajura</option>
                        <option value="Bagmati" {{ old('region', $address->region ?? '') === 'Bagmati' ? 'selected' : '' }}>Bagmati</option>
                        <option value="Koshi" {{ old('region', $address->region ?? '') === 'Koshi' ? 'selected' : '' }}>Koshi</option>
                        <option value="Madhesh" {{ old('region', $address->region ?? '') === 'Madhesh' ? 'selected' : '' }}>Madhesh</option>
                        <option value="Gandaki" {{ old('region', $address->region ?? '') === 'Gandaki' ? 'selected' : '' }}>Gandaki</option>
                        <option value="Lumbini" {{ old('region', $address->region ?? '') === 'Lumbini' ? 'selected' : '' }}>Lumbini</option>
                        <option value="Karnali" {{ old('region', $address->region ?? '') === 'Karnali' ? 'selected' : '' }}>Karnali</option>
                        <option value="Sudurpashchim" {{ old('region', $address->region ?? '') === 'Sudurpashchim' ? 'selected' : '' }}>Sudurpashchim</option>
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
                              placeholder="House no./building/street/area" required>{{ old('address', $address->address) }}</textarea>
                    @error('address')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Landmark (Optional) -->
                <div>
                    <label for="landmark" class="block text-sm font-medium text-gray-700 mb-1">Landmark (Optional)</label>
                    <input type="text" id="landmark" name="landmark" value="{{ old('landmark', $address->landmark) }}" 
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
                            <input type="radio" name="address_type" value="Home" {{ old('address_type', $address->address_type) == 'Home' ? 'checked' : '' }} class="text-[#c9a84c] focus:ring-[#c9a84c]">
                            <span class="text-sm text-gray-700">Home</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="radio" name="address_type" value="Office" {{ old('address_type', $address->address_type) == 'Office' ? 'checked' : '' }} class="text-[#c9a84c] focus:ring-[#c9a84c]">
                            <span class="text-sm text-gray-700">Office</span>
                        </label>
                    </div>
                </div>
                
                <!-- Default Toggles -->
                <div class="space-y-3 pt-2 border-t border-gray-100">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Default Shipping Address</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_default_shipping" value="1" class="sr-only peer" {{ old('is_default_shipping', $address->is_default_shipping) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#c9a84c]"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Default Billing Address</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_default_billing" value="1" class="sr-only peer" {{ old('is_default_billing', $address->is_default_billing ?? false) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#c9a84c]"></div>
                        </label>
                    </div>
                </div>
                
                <div class="pt-4">
                    <button type="submit" class="w-full px-6 py-3.5 bg-[#f97316] text-white font-bold rounded-lg hover:bg-[#ea580c] transition-all shadow-md">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection