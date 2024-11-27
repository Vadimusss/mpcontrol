import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import AddProductListForm from '@/Pages/ProductLists/Components/Forms/AddProductListForm';
import ProductListCard from '@/Pages/ProductLists/Components/ProductListCard';
import Modal from '@/Components/Modal';
import { Head, usePage } from '@inertiajs/react';

export default function ProductLists({ shop, ownProductLists, productLists }) {
    const { auth } = usePage().props;
    const [addProductListModalIsOpen, setAddProductListModalIsOpen] = useState(false);

    // const ownProductLists = productLists.filter((ProductList) => ProductList.creator.id);

    const handleAddProductListButtonClick = () => {
        setAddProductListModalIsOpen(true);
    };

    const closeAddProductListModal = (() => {
        setAddProductListModalIsOpen(false);
    });

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

            <div className="max-w-2xl mx-auto">
                <div className="p-2 sm:p-3 lg:p-6">
                    {productLists.length !== 0 && <h2 className="text-xl font-bold mb-3">Списки товаров магазина</h2>}
                    {productLists &&
                        productLists.map((productList) =>
                            <ProductListCard shopId={shop.id} productList={productList} key={productList.id} />)}
                    {ownProductLists && <h2 className="text-xl font-bold mb-3">Мои списки товаров</h2>}
                    {ownProductLists &&
                        ownProductLists.map((productList) =>
                            <ProductListCard shopId={shop.id} productList={productList} key={productList.id} />)}
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
