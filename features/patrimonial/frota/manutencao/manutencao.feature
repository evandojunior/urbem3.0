# features/patrimonial/frota//manutencao/manutencao.feature
Feature: Homepage Patrimonial>Frota>Manutenção
  In order to Homepage Patrimonial>Frota>Manutenção
  I would be able to access the urbem

  # Tipo 'Outros'
  Scenario: Create a new Manutencao with Tipo Manutencao 'Outros' with success
    Given I am authenticated as "suporte" with "123"
    Given I am on "/patrimonial/frota/manutencao/create"
    And I fill field with uniqueId as "tipoManutencao_1" with "2" when field is "input"
    And I fill field with uniqueId as "codVeiculo" with "1 - IJJ9373 - VolksWagem - Kombi" when field is "select"
    And I fill field with uniqueId as "dtManutencao" with "26/10/2016" when field is "input"
    And I fill field with uniqueId as "observacao" with "##Teste 1##" when field is "input"
    And I press "btn_create_and_list"
    Then I should see "criado com sucesso"

  Scenario: Create a new Manutencao with Tipo Manutencao 'Outros' and Empenho with success
    Given I am authenticated as "suporte" with "123"
    Given I am on "/patrimonial/frota/manutencao/create"
    And I fill field with uniqueId as "tipoManutencao_1" with "2" when field is "input"
    And I fill field with uniqueId as "codVeiculo" with "1 - IJJ9373 - VolksWagem - Kombi" when field is "select"
    And I fill field with uniqueId as "dtManutencao" with "26/10/2016" when field is "input"
    And I fill field with uniqueId as "observacao" with "##Teste 2##" when field is "input"
    And I fill field with uniqueId as "exercicio" with "2016" when field is "input"
    And I fill field with uniqueId as "codEntidade" with "2 - PREFEITURA MUNICIPAL DE MARIANA PIMENTEL" when field is "select"
    And I fill field with uniqueId as "codEmpenho" with "1" when field is "input"
    And I press "btn_create_and_list"
    Then I should see "criado com sucesso"

  Scenario: Create a new Manutencao with Tipo Manutencao 'Outros' and Empenho with failure
    Given I am authenticated as "suporte" with "123"
    Given I am on "/patrimonial/frota/manutencao/create"
    And I fill field with uniqueId as "tipoManutencao_1" with "2" when field is "input"
    And I fill field with uniqueId as "codVeiculo" with "1 - IJJ9373 - VolksWagem - Kombi" when field is "select"
    And I fill field with uniqueId as "dtManutencao" with "26/10/2016" when field is "input"
    And I fill field with uniqueId as "exercicio" with "2016" when field is "input"
    And I fill field with uniqueId as "codEntidade" with "1 - CAMARA MUNICIPAL DE VEREADORES" when field is "select"
    And I fill field with uniqueId as "codEmpenho" with "1" when field is "input"
    And I press "btn_create_and_list"
    Then I should see "Empenho não encontrado."


  Scenario: Edit a Manutencao with Tipo Manutencao 'Outros' with success
    Given I am authenticated as "suporte" with "123"
    Given I am on "/patrimonial/frota/manutencao/list"
    And I fill in "filter_observacao_value" with "##Teste 1##"
    And I press "search"
    And I follow "Detalhe"
    And I follow "edit"
    And I fill field with uniqueId as "tipoManutencao_1" with "2" when field is "input"
    And I fill field with uniqueId as "codVeiculo" with "2 - IVP1084 - Ford - Fiesta Hatch 1.0" when field is "select"
    And I fill field with uniqueId as "dtManutencao" with "26/10/2016" when field is "input"
    And I fill field with uniqueId as "observacao" with "##Teste 1 - Editado##" when field is "input"
    And I press "Salvar"
    Then I should see "O item foi atualizado com sucesso."

  Scenario: Edit a Manutencao with Tipo Manutencao 'Outros' and Empenho with success
    Given I am authenticated as "suporte" with "123"
    Given I am on "/patrimonial/frota/manutencao/list"
    And I fill in "filter_observacao_value" with "##Teste 2##"
    And I press "search"
    And I follow "Detalhe"
    And I follow "edit"
    And I fill field with uniqueId as "tipoManutencao_1" with "2" when field is "input"
    And I fill field with uniqueId as "codVeiculo" with "2 - IVP1084 - Ford - Fiesta Hatch 1.0" when field is "select"
    And I fill field with uniqueId as "dtManutencao" with "26/10/2016" when field is "input"
    And I fill field with uniqueId as "observacao" with "##Teste 2 - Editado##" when field is "input"
    And I fill field with uniqueId as "exercicio" with "2016" when field is "input"
    And I fill field with uniqueId as "codEntidade" with "2 - PREFEITURA MUNICIPAL DE MARIANA PIMENTEL" when field is "select"
    And I fill field with uniqueId as "codEmpenho" with "1" when field is "input"
    And I press "Salvar"
    Then I should see "O item foi atualizado com sucesso."

  Scenario: Edit a Manutencao with Tipo Manutencao 'Outros' and Empenho with failure
    Given I am authenticated as "suporte" with "123"
    Given I am on "/patrimonial/frota/manutencao/list"
    And I fill in "filter_observacao_value" with "##Teste 2 - Editado##"
    And I press "search"
    And I follow "Detalhe"
    And I follow "edit"
    And I fill field with uniqueId as "tipoManutencao_1" with "2" when field is "input"
    And I fill field with uniqueId as "codVeiculo" with "2 - IVP1084 - Ford - Fiesta Hatch 1.0" when field is "select"
    And I fill field with uniqueId as "dtManutencao" with "26/10/2016" when field is "input"
    And I fill field with uniqueId as "exercicio" with "2016" when field is "input"
    And I fill field with uniqueId as "codEntidade" with "1 - CAMARA MUNICIPAL DE VEREADORES" when field is "select"
    And I fill field with uniqueId as "codEmpenho" with "1" when field is "input"
    And I press "Salvar"
    Then I should see "Empenho não encontrado."


  Scenario: Annulment a Manutencao with Tipo Manutencao 'Outros' with success
    Given I am authenticated as "suporte" with "123"
    Given I am on "/patrimonial/frota/manutencao/list"
    And I fill in "filter_observacao_value" with "##Teste 1 - Editado##"
    And I press "search"
    And I follow "Detalhe"
    And I follow "Anular Manutenção"
    And I fill field with uniqueId as "observacao" with "##Teste 1 - Anulação##" when field is "input"
    And I press "Salvar"
    Then I should see "Anulação Manutenção"

  # Tipo 'Autorizacao de Abastecimento'
  Scenario: Create a new Manutencao with Tipo Manutencao 'Autorizacao de Abastecimento' with success
    Given I am authenticated as "suporte" with "123"
    Given I am on "/patrimonial/frota/manutencao/create"
    And I fill field with uniqueId as "tipoManutencao_0" with "1" when field is "input"
    And I fill field with uniqueId as "codAutorizacao" with "2/2016" when field is "select"
    And I fill field with uniqueId as "codVeiculo" with "2 - IVP1084 - Ford - Fiesta Hatch 1.0" when field is "select"
    And I fill field with uniqueId as "dtManutencao" with "26/10/2016" when field is "input"
    And I fill field with uniqueId as "observacao" with "##Teste 3##" when field is "input"
    And I press "btn_create_and_list"
    Then I should see "foi criado com sucesso."

  Scenario: Create a new Manutencao with Tipo Manutencao 'Autorizacao de Abastecimento' with failure
    Given I am authenticated as "suporte" with "123"
    Given I am on "/patrimonial/frota/manutencao/create"
    And I fill field with uniqueId as "tipoManutencao_0" with "1" when field is "input"
    And I fill field with uniqueId as "codAutorizacao" with "1/2016" when field is "select"
    And I fill field with uniqueId as "codVeiculo" with "2 - IVP1084 - Ford - Fiesta Hatch 1.0" when field is "select"
    And I fill field with uniqueId as "dtManutencao" with "26/10/2016" when field is "input"
    And I press "btn_create_and_list"
    Then I should see "Autorização informada já está em uso."

  Scenario: Create a new Manutencao with Tipo Manutencao 'Autorizacao de Abastecimento' and Empenho with success
    Given I am authenticated as "suporte" with "123"
    Given I am on "/patrimonial/frota/manutencao/create"
    And I fill field with uniqueId as "tipoManutencao_0" with "1" when field is "input"
    And I fill field with uniqueId as "codAutorizacao" with "3/2016" when field is "select"
    And I fill field with uniqueId as "codVeiculo" with "2 - IVP1084 - Ford - Fiesta Hatch 1.0" when field is "select"
    And I fill field with uniqueId as "dtManutencao" with "26/10/2016" when field is "input"
    And I fill field with uniqueId as "observacao" with "##Teste 4##" when field is "input"
    And I fill field with uniqueId as "exercicio" with "2016" when field is "input"
    And I fill field with uniqueId as "codEntidade" with "2 - PREFEITURA MUNICIPAL DE MARIANA PIMENTEL" when field is "select"
    And I fill field with uniqueId as "codEmpenho" with "1" when field is "input"
    And I press "btn_create_and_list"
    Then I should see "criado com sucesso"

  Scenario: Create a new Manutencao with Tipo Manutencao 'Autorizacao de Abastecimento' and Empenho with failure
    Given I am authenticated as "suporte" with "123"
    Given I am on "/patrimonial/frota/manutencao/create"
    And I fill field with uniqueId as "tipoManutencao_0" with "1" when field is "input"
    And I fill field with uniqueId as "codAutorizacao" with "1/2016" when field is "select"
    And I fill field with uniqueId as "codVeiculo" with "2 - IVP1084 - Ford - Fiesta Hatch 1.0" when field is "select"
    And I fill field with uniqueId as "dtManutencao" with "26/10/2016" when field is "input"
    And I fill field with uniqueId as "exercicio" with "2016" when field is "input"
    And I fill field with uniqueId as "codEntidade" with "1 - CAMARA MUNICIPAL DE VEREADORES" when field is "select"
    And I fill field with uniqueId as "codEmpenho" with "1" when field is "input"
    And I press "btn_create_and_list"
    Then I should see "Empenho não encontrado."


  Scenario: Edit a Manutencao with Tipo Manutencao 'Autorizacao de Abastecimento' with success
    Given I am authenticated as "suporte" with "123"
    Given I am on "/patrimonial/frota/manutencao/list"
    And I fill in "filter_observacao_value" with "##Teste 3##"
    And I press "search"
    And I follow "Detalhe"
    And I follow "edit"
    And I fill field with uniqueId as "tipoManutencao_0" with "1" when field is "input"
    And I fill field with uniqueId as "codAutorizacao" with "2/2016" when field is "select"
    And I fill field with uniqueId as "codVeiculo" with "2 - IVP1084 - Ford - Fiesta Hatch 1.0" when field is "select"
    And I fill field with uniqueId as "dtManutencao" with "26/10/2016" when field is "input"
    And I fill field with uniqueId as "observacao" with "##Teste 3 - Editado##" when field is "input"
    And I press "Salvar"
    Then I should see "O item foi atualizado com sucesso."

  Scenario: Edit a Manutencao with Tipo Manutencao 'Autorizacao de Abastecimento' with failure
    Given I am authenticated as "suporte" with "123"
    Given I am on "/patrimonial/frota/manutencao/list"
    And I fill in "filter_observacao_value" with "##Teste 3 - Editado##"
    And I press "search"
    And I follow "Detalhe"
    And I follow "edit"
    And I fill field with uniqueId as "tipoManutencao_0" with "1" when field is "input"
    And I fill field with uniqueId as "codAutorizacao" with "1/2016" when field is "select"
    And I fill field with uniqueId as "codVeiculo" with "2 - IVP1084 - Ford - Fiesta Hatch 1.0" when field is "select"
    And I fill field with uniqueId as "dtManutencao" with "26/10/2016" when field is "input"
    And I press "Salvar"
    Then I should see "Autorização informada já está em uso."

  Scenario: Edit a Manutencao with Tipo Manutencao 'Autorizacao de Abastecimento' and Empenho with success
    Given I am authenticated as "suporte" with "123"
    Given I am on "/patrimonial/frota/manutencao/list"
    And I fill in "filter_observacao_value" with "##Teste 4##"
    And I press "search"
    And I follow "Detalhe"
    And I follow "edit"
    And I fill field with uniqueId as "tipoManutencao_0" with "1" when field is "input"
    And I fill field with uniqueId as "codAutorizacao" with "3/2016" when field is "select"
    And I fill field with uniqueId as "codVeiculo" with "2 - IVP1084 - Ford - Fiesta Hatch 1.0" when field is "select"
    And I fill field with uniqueId as "dtManutencao" with "26/10/2016" when field is "input"
    And I fill field with uniqueId as "observacao" with "##Teste 4 - Editado##" when field is "input"
    And I fill field with uniqueId as "exercicio" with "2016" when field is "input"
    And I fill field with uniqueId as "codEntidade" with "2 - PREFEITURA MUNICIPAL DE MARIANA PIMENTEL" when field is "select"
    And I fill field with uniqueId as "codEmpenho" with "1" when field is "input"
    And I press "Salvar"
    Then I should see "O item foi atualizado com sucesso."

  Scenario: Edit a Manutencao with Tipo Manutencao 'Autorizacao de Abastecimento' and Empenho with failure
    Given I am authenticated as "suporte" with "123"
    Given I am on "/patrimonial/frota/manutencao/list"
    And I fill in "filter_observacao_value" with "##Teste 4 - Editado##"
    And I press "search"
    And I follow "Detalhe"
    And I follow "edit"
    And I fill field with uniqueId as "tipoManutencao_0" with "1" when field is "input"
    And I fill field with uniqueId as "codAutorizacao" with "3/2016" when field is "select"
    And I fill field with uniqueId as "codVeiculo" with "2 - IVP1084 - Ford - Fiesta Hatch 1.0" when field is "select"
    And I fill field with uniqueId as "dtManutencao" with "26/10/2016" when field is "input"
    And I fill field with uniqueId as "exercicio" with "2016" when field is "input"
    And I fill field with uniqueId as "codEntidade" with "1 - CAMARA MUNICIPAL DE VEREADORES" when field is "select"
    And I fill field with uniqueId as "codEmpenho" with "1" when field is "input"
    And I press "Salvar"
    Then I should see "Empenho não encontrado."


  Scenario: Annulment a Manutencao with Tipo Manutencao 'Autorizacao de Abastecimento' with success
    Given I am authenticated as "suporte" with "123"
    Given I am on "/patrimonial/frota/manutencao/list"
    And I fill in "filter_observacao_value" with "##Teste 3 - Editado##"
    And I press "search"
    And I follow "Detalhe"
    And I follow "Anular Manutenção"
    And I fill field with uniqueId as "observacao" with "##Teste 2 - Anulação##" when field is "input"
    And I press "Salvar"
    Then I should see "Anulação Manutenção"


  Scenario: Annulment a Manutencao the others tests with success
    Given I am authenticated as "suporte" with "123"
    Given I am on "/patrimonial/frota/manutencao/list"
    And I fill in "filter_observacao_value" with "##Teste 2 - Editado##"
    And I press "search"
    And I follow "Detalhe"
    And I follow "Anular Manutenção"
    And I fill field with uniqueId as "observacao" with "##Teste 3 - Anulação##" when field is "input"
    And I press "Salvar"
    Then I should see "Anulação Manutenção"

  Scenario: Annulment a Manutencao the others tests with success
    Given I am authenticated as "suporte" with "123"
    Given I am on "/patrimonial/frota/manutencao/list"
    And I fill in "filter_observacao_value" with "##Teste 4 - Editado##"
    And I press "search"
    And I follow "Detalhe"
    And I follow "Anular Manutenção"
    And I fill field with uniqueId as "observacao" with "##Teste 4 - Anulação##" when field is "input"
    And I press "Salvar"
    Then I should see "Anulação Manutenção"
