import { useState, useMemo } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import Modal from '@/Components/Modal';
import { Head } from '@inertiajs/react';

export default function WorkSpaces({ auth, shop, reports, goodLists }) {
    console.log(shop);
    console.log(reports);
    console.log(goodLists);
    return (
        <AuthenticatedLayout
            navigation={true}
            shopId={shop.id}
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    {shop.name}
                </h2>
            }>
            <Head title={`Магазин ${shop.name}`} />
            <p>Test!</p>

        </AuthenticatedLayout>
    );
}
