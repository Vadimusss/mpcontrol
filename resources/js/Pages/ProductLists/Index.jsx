import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import AddProductListForm from '@/Pages/ProductLists/Components/Forms/AddProductListForm';
import Modal from '@/Components/Modal';
import { Head } from '@inertiajs/react';

export default function ProductLists({ shop, productLists }) {
    const [addProductListModalIsOpen, setAddProductListModalIsOpen] = useState(false);

    const handleAddProductListButtonClick = () => {
        setAddProductListModalIsOpen(true);
    };

    const closeAddProductListModal = (() => {
        setAddProductListModalIsOpen(false);
    });

    return (
        <AuthenticatedLayout
            navigation={true}
            shopId = {shop.id}
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    {shop.name}
                </h2>
            }>
            <Head title={`Магазин ${shop.name}`} />

            <div className="max-w-2xl mx-auto">
                <div className="p-2 sm:p-3 lg:p-6">
                    <h2 className="text-xl font-bold mb-3">Списки товаров</h2>
                    {productLists && productLists.map((productList) =>
                        <p>{productList.name}</p>
                    )}
                    <PrimaryButton
                        className="mt-4"
                        onClick={(e) => handleAddProductListButtonClick(e)}>
                        Добавить список товаров
                    </PrimaryButton>
                </div>
            </div>
            <Modal show={addProductListModalIsOpen} onClose={closeAddProductListModal}>
                <AddProductListForm shopId={shop.id} closeModal={() => closeAddProductListModal()} />
            </Modal>
        </AuthenticatedLayout>
    );
}
