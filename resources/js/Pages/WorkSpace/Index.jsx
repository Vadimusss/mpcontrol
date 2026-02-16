import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function WorkSpace({ shop, workSpace, goods }) {
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