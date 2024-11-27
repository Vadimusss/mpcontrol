import { useState } from 'react';
import PrimaryButton from '@/Components/PrimaryButton';
import Modal from '@/Components/Modal';
import DeleteProductListConfirmModal from '@/Pages/ProductLists/Components/Modals/DeleteProductListConfirmModal';
import { usePage } from '@inertiajs/react';
import { Inertia } from '@inertiajs/inertia';

export default function ProductListCard({ shopId, productList }) {
    const { auth } = usePage().props;
    const [modalState, setModalIState] = useState({
        changeSettingModalIsOpen: false,
        deleteConfirmModalIsOpen: false,
    });

    const openChangeSettingModal = (() => {
        setModalIState({ changeSettingModalIsOpen: true });
    });

    const closeChangeSettingModal = (() => {
        setModalIState({ changeSettingModalIsOpen: false });
    });

    const openDeleteModal = (() => {
        setModalIState({ deleteConfirmModalIsOpen: true });
    });

    const closeDeleteModal = (() => {
        setModalIState({ deleteConfirmModalIsOpen: false });
    });

    const userIsCreator = productList.creator.id === auth.user.id;

    return (
        <div className="border border-gray-300 rounded-md shadow-sm bg-white mb-2 p-2">
            <p>ID: {productList.id}</p>
            <p>{productList.name}</p>
            <p>Создатель: {productList.creator.name}</p>
            <div className="flex flex-col">
                <div className="flex gap-x-2">
                    <PrimaryButton
                        className="mt-4 max-w-fit"
                        onClick={(e) => Inertia.get(route('shops.productlists.show', productList.id))}>
                        {userIsCreator ? 'Редактировать' : 'Посмотреть'}
                    </PrimaryButton>
                    {(productList.creator.id === auth.user.id) &&
                        <PrimaryButton
                            className="mt-4 max-w-fit"
                            onClick={(e) => openDeleteModal()}>
                            Удалить
                        </PrimaryButton>}
                </div>
                <Modal show={modalState.changeSettingModalIsOpen} onClose={closeChangeSettingModal}>
                    <p>Test!!!</p>
                </Modal>
                <DeleteProductListConfirmModal
                    shopId={shopId}
                    productList={productList}
                    maxWidth={'xl'}
                    IsOpen={modalState.deleteConfirmModalIsOpen}
                    closeModal={closeDeleteModal} />
            </div>
        </div >
    );
}
