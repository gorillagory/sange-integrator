<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('System Core: Authorized Vaults') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @if (session('error'))
                    <div class="mb-8 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
                        <p class="font-bold">Security Gateway Alert</p>
                        <p>{{ session('error') }}</p>
                    </div>
                @endif
                @forelse ($companies as $company)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-bold text-gray-900">{{ $company->name }}</h3>
                                <span class="px-3 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full">
                                    {{ $company->role }}
                                </span>
                            </div>

                            <p class="text-sm text-gray-500 mb-6">
                                Subdomain: <span class="font-mono text-gray-800">{{ $company->subdomain }}</span><br>
                                DB Status: <span class="text-green-600 font-semibold">Online</span>
                            </p>

                            <a href="http://{{ $company->subdomain }}.bayam.test:8000/dashboard"
                               class="inline-flex items-center justify-center w-full px-4 py-2 bg-gray-900 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Enter Vault &rarr;
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center text-gray-500">
                        You do not have clearance for any operational vaults. Contact System Administrator.
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
