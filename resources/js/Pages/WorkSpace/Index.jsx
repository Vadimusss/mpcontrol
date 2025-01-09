import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import SimpleTable from './Widgets/SimpleTable';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, usePage } from '@inertiajs/react';
import { Inertia } from '@inertiajs/inertia';

export default function WorkSpace({ shop, workSpace, goods }) {
/*     console.log(shop);
    console.log(workSpace);
    console.log(goods); */
    return (
        <AuthenticatedLayout
            navigation={true}
            shopId={shop.id}
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    {shop.name}/{workSpace.name}
                </h2>
            }>
            <Head title={`Магазин ${shop.name}`} />
            <div className="max-w-2xl mx-auto pt-4">
                <SimpleTable goods={goods} />
            </div>
        </AuthenticatedLayout>
    );
}
