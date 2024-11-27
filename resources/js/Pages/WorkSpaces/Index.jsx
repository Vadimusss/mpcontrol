import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import AddWorkSpaceForm from '@/Pages/WorkSpaces/Components/Forms/AddWorkSpaceForm';
import Modal from '@/Components/Modal';
import WorkSpaceCard from '@/Pages/WorkSpaces/Components/WorkSpaceCard';
import { Head } from '@inertiajs/react';

export default function WorkSpaces({ shop, ownWorkSpaces, workSpaces }) {
    const [addWorkSpaceModalIsOpen, setAddWorkSpaceModalIsOpen] = useState(false);

    const handleAddWorkSpaceButtonClick = () => {
        setAddWorkSpaceModalIsOpen(true);
    };

    const closeAddWorkSpaceModal = (() => {
        setAddWorkSpaceModalIsOpen(false);
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
                    {workSpaces && <h2 className="text-xl font-bold mb-3">Рабочие области магазина</h2>}
                    {workSpaces &&
                        workSpaces.map((workSpace) =>
                            <WorkSpaceCard shopId={shop.id} workSpace={workSpace} key={workSpace.id} />)}
                    {ownWorkSpaces && <h2 className="text-xl font-bold mb-3">Мои рабочие области</h2>}
                    {ownWorkSpaces &&
                        ownWorkSpaces.map((workSpace) =>
                            <WorkSpaceCard shopId={shop.id} workSpace={workSpace} key={workSpace.id} />)}
                    <PrimaryButton
                        className="mt-4"
                        onClick={() => handleAddWorkSpaceButtonClick()}>
                        Добавить рабочую область
                    </PrimaryButton>
                </div>
            </div>
            <Modal show={addWorkSpaceModalIsOpen} onClose={closeAddWorkSpaceModal}>
                <AddWorkSpaceForm shopId={shop.id} closeModal={() => closeAddWorkSpaceModal()} />
            </Modal>
        </AuthenticatedLayout>
    );
}
