@extends('layouts.app')

@section('title', 'Marcas - CompareWare')

@section('content')
<div class="px-40 flex flex-1 justify-center py-5">
    <div class="layout-content-container flex flex-col max-w-[960px] flex-1">
        <div class="flex flex-wrap justify-between gap-3 p-4">
            <p class="text-[#0d141c] tracking-light text-[32px] font-bold leading-tight min-w-72">Marcas</p>
        </div>
        <div class="px-4 py-3">
            <label class="flex flex-col min-w-40 h-12 w-full">
                <div class="flex w-full flex-1 items-stretch rounded-lg h-full">
                    <div
                        class="text-[#49739c] flex border-none bg-[#e7edf4] items-center justify-center pl-4 rounded-l-lg border-r-0"
                        data-icon="MagnifyingGlass"
                        data-size="24px"
                        data-weight="regular"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" fill="currentColor" viewBox="0 0 256 256">
                            <path
                                d="M229.66,218.34l-50.07-50.06a88.11,88.11,0,1,0-11.31,11.31l50.06,50.07a8,8,0,0,0,11.32-11.32ZM40,112a72,72,0,1,1,72,72A72.08,72.08,0,0,1,40,112Z"
                            ></path>
                        </svg>
                    </div>
                    <input
                        placeholder="Buscar marca"
                        class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#0d141c] focus:outline-0 focus:ring-0 border-none bg-[#e7edf4] focus:border-none h-full placeholder:text-[#49739c] px-4 rounded-l-none border-l-0 pl-2 text-base font-normal leading-normal"
                        value=""
                    />
                </div>
            </label>
        </div>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(158px,1fr))] gap-3 p-4">
            <div class="flex flex-col gap-3 pb-3">
                <div
                    class="w-full bg-center bg-no-repeat aspect-square bg-cover rounded-lg"
                    style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuCMhwOPMYN-gZGiguDFMB1F4ZwiHg9nFGdE86M8-ZDxlSuq5TlnNJSaxFtKGE7uxMUgWbxmjNLTDJH10JHcc11y7GVrdbxnbe2Lh2dVPvKAwxnMfW06NrUU1eZmONqr5jn5aSnKTzqtBQSDg3xTyeH2Uv6vufncZxAQzlKEGiqxM4b4ARHmjg-PlnXAKYn_Mp9nGNp1dCqtSPmyCnJNi7Riurd6BVEqbXaPn60PYGJVhM0VNuTkxZ1KS58U0_MJuNy5AkZge3rugmQ");'
                ></div>
                <p class="text-[#0d141c] text-base font-medium leading-normal">TechMaster</p>
            </div>
            <div class="flex flex-col gap-3 pb-3">
                <div
                    class="w-full bg-center bg-no-repeat aspect-square bg-cover rounded-lg"
                    style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuAckuDdU8wGKpvLzty9HjkNL-lg7uX-XMzdSSFpZYYQUoOGTsDEma1q3Ks6P-tj6P-v4cNe8OcxefSylUoVQjqEGl5zpjaUDO2T2ZngP_OTHYcydjXle13GdgVh2sOvQ6GeAHe2cEYh3Wbd7dzB052SYBRjElsRreSE-shrdbHy-uYCJqGUotYBHPCRnNEm60d9jFUqapuuVED4TRG3mwIG9TAoCY74um3xRd0y72LFnsGp1Lr9C-3hMnXlllWlJ7ktS0IVH8h-7jY");'
                ></div>
                <p class="text-[#0d141c] text-base font-medium leading-normal">SonicGear</p>
            </div>
            <div class="flex flex-col gap-3 pb-3">
                <div
                    class="w-full bg-center bg-no-repeat aspect-square bg-cover rounded-lg"
                    style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuAi0vs0yy1nc1an2Rch2ZWh_OuOjclO1NNWHxI3ClVf_Ot2JrLaKjv-6BCwyjqVPEhNlbplqazZB02boRl_556o5eaaxw0WhcvqtcdW-GkanoREmsGwdMupx9MHolhQaOQPpY3GgpsP8jHSfdKglqMA6jzXVZsWFprUx4zRtM1bVDBT9-PnMZ8AxnccMy05oQf9YFH2WjKJRQdG2c2rDWHoz7za6p7OS3bdDjYBUScBbXSgDkzdpB2GKs3Ia4YyPZJIG4Pc-4GDTXA");'
                ></div>
                <p class="text-[#0d141c] text-base font-medium leading-normal">ErgoTech</p>
            </div>
            <div class="flex flex-col gap-3 pb-3">
                <div
                    class="w-full bg-center bg-no-repeat aspect-square bg-cover rounded-lg"
                    style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuCgP60L_6oN1moeDCQEilEVa9endYmodroEyYzqHTWgNeX_PmntR8xdzfmIB0NRDhCjJz8tXDpv248DhoKkio00FBMdDndeMDGreG8rJvb5nQeGHpmpe0SPHJWd1AW-1MgjhhnBd7-Zn3bILj4AbITds3bJL9wbKUvzQ76HhkFVwuSvb5uIPVB8drEQDd0JE0eJiawiNa2JKehrGG6cJZ-SEibQR3xw_tz5VPLLze4FFMg4xaTp3hHdC5XKjyHLO1cwCReyRx5k9qc");'
                ></div>
                <p class="text-[#0d141c] text-base font-medium leading-normal">ClickCraft</p>
            </div>
            <div class="flex flex-col gap-3 pb-3">
                <div
                    class="w-full bg-center bg-no-repeat aspect-square bg-cover rounded-lg"
                    style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuDNYzPtJmYdbNw2oFC3wpORSM-alsCBLA4696Nl47tNawRlkxZ3CPOClxAZ2meKRgL1Z6KF5EJ1wh_oknqQYsfhp7l4iMvW9o2hqnijgGnUK1meTd_R5CRcZIQtT197OEQOUrRwC3WPj98ZGPIjvdPJdiKmbzxUJkGi4kGyQvvIjIiIG98gHsv5pVYmwRxwpTBjjaf1_4YC5D-L7AJY-y3QSpk4QD-7etN8MPVohg8xGfrdhy9T9kBUMYCYH9lo-ePOiVQ2rTukFVU");'
                ></div>
                <p class="text-[#0d141c] text-base font-medium leading-normal">AudioWave</p>
            </div>
            <div class="flex flex-col gap-3 pb-3">
                <div
                    class="w-full bg-center bg-no-repeat aspect-square bg-cover rounded-lg"
                    style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuBrEu3xe2_q6_ZP6PrmGuDBp-W81CGBhOFRsbhE2_GbT36cnvl-Dpp4lBFsB5l-Yg2qNLj0TVNgJ9H0jvVRBSZqy7RWjuOrhZOJAM59zISpJxKTutXiVWcsusDcaq2I4thfUON_2SsiBB_Hdl7HQVdLWzbd9ktHnXzPJg2QYXRe6hpxXEWW-fGvpwcxtLXHhn8zgwI4q6kcTBUDozoydkQlMnzPC6MuwvUkJspCHoc2rFRy6mXk4_JjTCYRR_ZeO4l3S0zzMwcXIHE");'
                ></div>
                <p class="text-[#0d141c] text-base font-medium leading-normal">Visionary</p>
            </div>
        </div>
    </div>
</div>
@endsection